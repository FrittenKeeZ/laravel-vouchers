<?php

declare(strict_types=1);

use FrittenKeeZ\Vouchers\Config;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOwnerToVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (!Schema::hasColumn(Config::table('vouchers'), 'owner_id')) {
            Schema::table(Config::table('vouchers'), function (Blueprint $table) {
                $table->nullableMorphs('owner');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        // We don't want to risk any data loss, so no reversal.
    }
}
