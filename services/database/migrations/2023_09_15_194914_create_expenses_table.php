<?php

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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->integer('exp_amount');
            $table->integer('quantity')->nullable();
            $table->integer('unit_price')->nullable();
            $table->string('comment', 200)->nullable();
            $table->date('expense_date');
            $table->string('invoice_path')->nullable();
            $table->char('repartition_id');
            $table->char('management_unit_id')->nullable();
            $table->char('create_id')->nullable();
            $table->char('update_id')->nullable();
            $table->timestamps();

            $table->foreign('repartition_id')->references('id')->on('repartitions');
            $table->foreign('management_unit_id')->references('id')->on('management_units');
            $table->foreign('create_id')->references('id')->on('users');
            $table->foreign('update_id')->references('id')->on('users');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
