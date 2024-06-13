<?php

declare(strict_types=1);

use FrittenKeeZ\Vouchers\Config;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(Config::table('vouchers'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code')->unique();
            $table->nullableMorphs('owner');
            $table->text('metadata')->nullable();
            $table->dateTime('starts_at')->nullable()->index();
            $table->dateTime('expires_at')->nullable()->index();
            $table->dateTime('redeemed_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create(Config::table('redeemers'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('voucher_id')->unsigned();
            $table->morphs('redeemer');
            $table->text('metadata')->nullable();
            $table->timestamps();

            // Foreign key references.
            $table
                ->foreign('voucher_id')
                ->references('id')
                ->on(Config::table('vouchers'))
                ->onDelete('cascade')
            ;
        });

        Schema::create(Config::table('entities'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('voucher_id')->unsigned();
            $table->morphs('entity');

            // Unique index.
            $table->unique(['voucher_id', 'entity_type', 'entity_id'], 'entity');

            // Foreign key references.
            $table
                ->foreign('voucher_id')
                ->references('id')
                ->on(Config::table('vouchers'))
                ->onDelete('cascade')
            ;
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(Config::table('entities'));
        Schema::dropIfExists(Config::table('redeemers'));
        Schema::dropIfExists(Config::table('vouchers'));
    }
};
