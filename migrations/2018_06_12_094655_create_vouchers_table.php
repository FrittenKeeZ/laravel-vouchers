<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->unique();
            $table->integer('user_id')->unsigned()->nullable();
            $table->text('metadata')->nullable();
            $table->dateTime('starts_at')->nullable()->index();
            $table->dateTime('expires_at')->nullable()->index();
            $table->dateTime('redeemed_at')->nullable()->index();
            $table->timestamps();

            // Foreign key references.
            $table
                ->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vouchers');
    }
}
