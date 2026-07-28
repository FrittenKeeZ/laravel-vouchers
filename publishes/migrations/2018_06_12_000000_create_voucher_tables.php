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
            $table->id();
            $table->string('code')->unique();
            $table->nullableMorphs('owner');
            $table->json('metadata')->nullable();
            $table->dateTime('starts_at')->nullable()->index();
            $table->dateTime('expires_at')->nullable()->index();
            $table->dateTime('redeemed_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create(Config::table('redeemers'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained(Config::table('vouchers'))->cascadeOnDelete();
            $table->morphs('redeemer');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create(Config::table('entities'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained(Config::table('vouchers'))->cascadeOnDelete();
            $table->morphs('entity');

            // Unique index.
            $table->unique(['voucher_id', 'entity_type', 'entity_id'], 'entity');
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
