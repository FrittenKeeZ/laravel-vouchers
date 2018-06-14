<?php

use FrittenKeeZ\Vouchers\Helpers;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVoucherTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Helpers::table('vouchers'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code')->unique();
            $table->text('metadata')->nullable();
            $table->dateTime('starts_at')->nullable()->index();
            $table->dateTime('expires_at')->nullable()->index();
            $table->dateTime('redeemed_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create(Helpers::table('redeemers'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('voucher_id')->unsigned();
            $table->morphs('redeemer');
            $table->text('metadata')->nullable();
            $table->timestamps();

            // Foreign key references.
            $table
                ->foreign('voucher_id')
                ->references('id')
                ->on(Helpers::table('vouchers'))
                ->onDelete('cascade');
        });

        Schema::create(Helpers::table('entities'), function (Blueprint $table) {
            $table->bigInteger('voucher_id')->unsigned();
            $table->morphs('entity');

            // Foreign key references.
            $table
                ->foreign('voucher_id')
                ->references('id')
                ->on(Helpers::table('vouchers'))
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(Helpers::table('entities'));
        Schema::dropIfExists(Helpers::table('redeemers'));
        Schema::dropIfExists(Helpers::table('vouchers'));
    }
}
