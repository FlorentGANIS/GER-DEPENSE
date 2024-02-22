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
        Schema::create('budgets', function (Blueprint $table) {
            $table->char('id', 8)->primary();
            $table->char('month_id');
            $table->integer('year_budget');
            $table->boolean('status')->default(true);
            $table->integer('global_amount');
            $table->integer('remaining_amount');
            $table->integer('total_incomes');
            $table->integer('total_expenses');
            $table->integer('balance');
            $table->boolean('is_shared')->default(false);
            $table->char('create_id')->nullable();
            $table->char('update_id')->nullable();
            $table->timestamps();
            
            $table->foreign('month_id')->references('id')->on('months');
            $table->foreign('create_id')->references('id')->on('users');
            $table->foreign('update_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
