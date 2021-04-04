<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Console\Commands;

use FrittenKeeZ\Vouchers\Concerns\HasVouchers;
use FrittenKeeZ\Vouchers\Models\Voucher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MigrateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vouchers:migrate
                            {--model=* : Specify one or more models to transfer from associated entities to owners}
                            {--folder=* : Specify one or more folders to search for transferable models}
                            {--mode=auto : Database operation mode - possible values: "auto", "retain", "delete"}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transfer first associated entity to owner of voucher';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $mode = $this->option('mode');
        $models = $this->getModels();

        // Validate input.
        $validator = Validator::make(compact('mode', 'models'), [
            'mode'   => ['required', 'in:auto,retain,delete'],
            'models' => ['required'],
        ]);

        if ($validator->fails()) {
            $this->info('Migration could not continue. See error messages below:');
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return 1;
        }

        // Output operation mode for good measure.
        $this->info(sprintf('Database operation mode is set to: <fg=yellow>%s</>', $mode));

        // Count number of vouchers available for migration.
        $counts = collect($models)->mapWithKeys(function (string $class) {
            return [$class => Voucher::withoutOwner()->withEntities($class)->count()];
        })->all();
        $rows = collect($counts)->map(function (int $count, string $class) {
            return [$class, $count];
        })->values()->all();
        $this->table(['Model', 'Vouchers Count'], $rows);

        if (array_sum($counts) > 0 && $this->confirm('Do you wish to continue?', true)) {
            foreach ($counts as $class => $count) {
                $bar = $this->output->createProgressBar($count);
                $bar->setFormat("%message%\n %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s%");

                $query = Voucher::withoutOwner()->withEntities($class);
                $alias = (new $class())->getMorphClass();
                foreach ($query->cursor() as $voucher) {
                    $bar->setMessage(sprintf(
                        'Migrating model <fg=yellow>%s</> - Voucher #%d',
                        $class,
                        $voucher->getKey()
                    ));

                    DB::transaction(function () use ($voucher, $alias, $mode) {
                        $owner = $voucher->voucherEntities->first(function ($entity) use ($alias) {
                            return $entity->entity_type === $alias;
                        });
                        // Set owner directly to prevent issues with deleted entities.
                        $voucher->owner_type = $owner->entity_type;
                        $voucher->owner_id = $owner->entity_id;
                        $voucher->save();

                        // Whether to delete the owner from entities.
                        if ($mode === 'delete' || $mode === 'auto' && $voucher->voucherEntities->count() === 1) {
                            $entities = $voucher->voucherEntities->reject(function ($entity) use ($owner) {
                                return $entity->entity_type === $owner->entity_type
                                    && $entity->entity_id === $owner->entity_id;
                            });
                            // Detach all existing entities.
                            $voucher->voucherEntities()->delete();
                            if ($entities->isNotEmpty()) {
                                // Mark entities as not existing.
                                $entities->each(function ($entity) {
                                    $entity->exists = false;
                                });
                                // Re-attach remaining entities.
                                $voucher->voucherEntities()->saveMany($entities);
                            }
                        }
                    });

                    $bar->advance();
                }

                $bar->finish();
                $this->line('');
            }
        }

        return 0;
    }

    /**
     * Get model classes.
     *
     * @return array
     */
    protected function getModels(): array
    {
        $models = $this->option('model');
        // Load models from folders if not directly specified.
        if (empty($models)) {
            $folders = $this->option('folder');
            if (empty($folders)) {
                // Fallback to common folders.
                $folders = ['app', 'app/Models'];
            }
            $this->info(sprintf(
                'Searching for models in folders: <fg=yellow>%s</>',
                implode('</>, <fg=yellow>', $folders)
            ));
            $models = collect($folders)
                ->map(function (string $folder) {
                    $path = Str::startsWith($folder, '/') ? $folder : base_path($folder);
                    // Ensure no invalid paths are searched.
                    if (!is_dir($path)) {
                        return [];
                    }

                    return collect(scandir($path))
                        ->filter(function (string $file) {
                            // Remove any non PHP files.
                            return Str::endsWith($file, '.php');
                        })
                        ->map(function (string $file) use ($path) {
                            $class = basename($file, '.php');
                            // Read first 200 bytes, should be enough to extract namespace.
                            $contents = file_get_contents($path . '/' . $file, false, null, 0, 200);
                            if (preg_match('/namespace\s+([^;]+);/i', $contents, $matches)) {
                                return $matches[1] . '\\' . $class;
                            }
                            // Fallback to the class itself.
                            return '\\' . $class;
                        })
                        ->values()
                        ->all()
                    ;
                })
                ->flatten()
                ->filter(function (string $class) {
                    $traits = class_uses_recursive($class);
                    // Ensure only models using the `HasVouchers` trait are included.
                    return !empty($traits) && \in_array(HasVouchers::class, $traits);
                })
                ->values()
                ->all()
            ;
        }

        return $models;
    }
}
