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
        Schema::create('repartition_income_budgets', function (Blueprint $table) {
            $table->char('id', 15)->primary();
            $table->char('repartition_id');
            $table->unsignedBigInteger('income_budget_id');
            $table->integer('amount_rep_inc_budget');
            $table->char('create_id');
            $table->timestamps();

            $table->foreign('repartition_id')->references('id')->on('repartitions');
            $table->foreign('income_budget_id')->references('id')->on('income_budgets');
            $table->foreign('create_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repartition_income_budgets');
    }
};
