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
        Schema::create('income_details', function (Blueprint $table) {
            $table->id();
            $table->string('label', 100);
            $table->integer('inc_det_amount');
            $table->char('budget_id');
            $table->char('income_id');
            $table->timestamps();

            $table->foreign('budget_id')->references('id')->on('budgets');
            $table->foreign('income_id')->references('id')->on('incomes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('income_details');
    }
};
