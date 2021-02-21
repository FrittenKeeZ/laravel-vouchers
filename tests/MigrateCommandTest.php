<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Tests;

use FrittenKeeZ\Vouchers\Facades\Vouchers;
use FrittenKeeZ\Vouchers\Models\Voucher;
use FrittenKeeZ\Vouchers\Tests\Models\Color;
use FrittenKeeZ\Vouchers\Tests\Models\User;

/**
 * @internal
 */
class MigrateCommandTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Skip command tests on anything below Laravel 8, as key features are missing.
        if (version_compare($this->app->version(), '8.0', '<')) {
            $this->markTestSkipped('Requires Laravel 8 to successfully complete these tests.');
        }
    }

    /**
     * Test arguments.
     *
     * @return void
     */
    public function testArguments(): void
    {
        $this->artisan('vouchers:migrate')
            ->expectsOutput('Searching for models in folders: app, app/Models')
            ->expectsOutput('Migration could not continue. See error messages below:')
            ->expectsOutput('The models field is required.')
            ->assertExitCode(1)
        ;

        $this->artisan('vouchers:migrate', ['--mode' => 'fake', '--folder' => ['app/Models', 'app/Fakes']])
            ->expectsOutput('Searching for models in folders: app/Models, app/Fakes')
            ->expectsOutput('Migration could not continue. See error messages below:')
            ->expectsOutput('The selected mode is invalid.')
            ->expectsOutput('The models field is required.')
            ->assertExitCode(1)
        ;

        $this->artisan('vouchers:migrate', ['--folder' => [__DIR__ . '/Models']])
            ->expectsOutput('Searching for models in folders: ' . __DIR__ . '/Models')
            ->expectsOutput('Database operation mode is set to: auto')
            ->expectsTable(['Model', 'Vouchers Count'], [[User::class, 0]])
            ->assertExitCode(0)
        ;

        $this->artisan('vouchers:migrate', ['--mode' => 'retain', '--model' => [Color::class]])
            ->expectsOutput('Database operation mode is set to: retain')
            ->expectsTable(['Model', 'Vouchers Count'], [[Color::class, 0]])
            ->assertExitCode(0)
        ;

        $this->artisan('vouchers:migrate', ['--mode' => 'delete', '--model' => [User::class]])
            ->expectsOutput('Database operation mode is set to: delete')
            ->expectsTable(['Model', 'Vouchers Count'], [[User::class, 0]])
            ->assertExitCode(0)
        ;
    }

    /**
     * Test migration with auto mode.
     *
     * @return void
     */
    public function testMigrationModeAuto(): void
    {
        $user = $this->factory(User::class)->create();

        // Create vouchers.
        Vouchers::create();
        Vouchers::withOwner($user)->create();
        $v1 = Vouchers::withEntities($user)->create();
        $v2 = Vouchers::withEntities($user, ...$this->factory(Color::class, 2)->create())->create();

        $this->assertSame(4, Voucher::count());
        $this->assertSame(3, Voucher::withoutOwner()->count());
        $this->assertSame(1, Voucher::withOwner($user)->count());
        $this->assertSame(2, $user->associatedVouchers()->count());

        $this->artisan('vouchers:migrate', ['--model' => [User::class]])
            ->expectsOutput('Database operation mode is set to: auto')
            ->expectsTable(['Model', 'Vouchers Count'], [[User::class, 2]])
            ->expectsConfirmation('Do you wish to continue?', 'yes')
            ->assertExitCode(0)
        ;

        // Refresh and check owners.
        $v1->refresh();
        $v2->refresh();
        $this->assertTrue($user->is($v1->owner));
        $this->assertSame(0, $v1->voucherEntities->count());
        $this->assertTrue($user->is($v2->owner));
        $this->assertSame(3, $v2->voucherEntities->count());
        $this->assertSame(1, $user->associatedVouchers()->count());
    }

    /**
     * Test migration with retain mode.
     *
     * @return void
     */
    public function testMigrationModeRetain(): void
    {
        $user = $this->factory(User::class)->create();

        // Create vouchers.
        Vouchers::create();
        Vouchers::withOwner($user)->create();
        $v1 = Vouchers::withEntities($user)->create();
        $v2 = Vouchers::withEntities($user, ...$this->factory(Color::class, 2)->create())->create();

        $this->assertSame(4, Voucher::count());
        $this->assertSame(3, Voucher::withoutOwner()->count());
        $this->assertSame(1, Voucher::withOwner($user)->count());
        $this->assertSame(2, $user->associatedVouchers()->count());

        $this->artisan('vouchers:migrate', ['--mode' => 'retain', '--model' => [User::class]])
            ->expectsOutput('Database operation mode is set to: retain')
            ->expectsTable(['Model', 'Vouchers Count'], [[User::class, 2]])
            ->expectsConfirmation('Do you wish to continue?', 'yes')
            ->assertExitCode(0)
        ;

        // Refresh and check owners.
        $v1->refresh();
        $v2->refresh();
        $this->assertTrue($user->is($v1->owner));
        $this->assertSame(1, $v1->voucherEntities->count());
        $this->assertTrue($user->is($v2->owner));
        $this->assertSame(3, $v2->voucherEntities->count());
        $this->assertSame(2, $user->associatedVouchers()->count());
    }

    /**
     * Test migration with delete mode.
     *
     * @return void
     */
    public function testMigrationModeDelete(): void
    {
        $user = $this->factory(User::class)->create();

        // Create vouchers.
        Vouchers::create();
        Vouchers::withOwner($user)->create();
        $v1 = Vouchers::withEntities($user)->create();
        $v2 = Vouchers::withEntities($user, ...$this->factory(Color::class, 2)->create())->create();

        $this->assertSame(4, Voucher::count());
        $this->assertSame(3, Voucher::withoutOwner()->count());
        $this->assertSame(1, Voucher::withOwner($user)->count());
        $this->assertSame(2, $user->associatedVouchers()->count());

        $this->artisan('vouchers:migrate', ['--mode' => 'delete', '--model' => [User::class]])
            ->expectsOutput('Database operation mode is set to: delete')
            ->expectsTable(['Model', 'Vouchers Count'], [[User::class, 2]])
            ->expectsConfirmation('Do you wish to continue?', 'yes')
            ->assertExitCode(0)
        ;

        // Refresh and check owners.
        $v1->refresh();
        $v2->refresh();
        $this->assertTrue($user->is($v1->owner));
        $this->assertSame(0, $v1->voucherEntities->count());
        $this->assertTrue($user->is($v2->owner));
        $this->assertSame(2, $v2->voucherEntities->count());
        $this->assertSame(0, $user->associatedVouchers()->count());
    }
}
