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
        Schema::create('income_budgets', function (Blueprint $table) {
            $table->id();
            $table->integer('ib_amount');
            $table->char('income_id');
            $table->char('budget_id');
            $table->char('create_id');
            $table->char('update_id')->nullable();
            $table->timestamps();

            $table->foreign('income_id')->references('id')->on('incomes');
            $table->foreign('budget_id')->references('id')->on('budgets');            
            $table->foreign('create_id')->references('id')->on('users');
            $table->foreign('update_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('income_budgets');
    }
};
