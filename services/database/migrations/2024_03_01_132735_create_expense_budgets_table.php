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
        Schema::create('expense_budgets', function (Blueprint $table) {
            $table->id();
            $table->char('budget_id');
            $table->char('repartition_id');
            $table->string('type');
            $table->integer('prevision');
            $table->integer('amount_used');
            $table->integer('envelope_help');
            $table->char('create_id');
            $table->char('update_id')->nullable();
            $table->timestamps();
            
            $table->foreign('repartition_id')->references('id')->on('repartitions');
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
        Schema::dropIfExists('expense_budgets');
    }
};
