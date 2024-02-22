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
        Schema::create('repartitions', function (Blueprint $table) {
            $table->char('id', 20)->primary();
            $table->char('budget_id');
            $table->char('category_id');
            $table->integer('rep_amount');
            $table->char('create_id')->nullable();
            $table->char('update_id')->nullable();
            $table->timestamps();
            
            $table->foreign('category_id')->references('id')->on('categories');
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
        Schema::dropIfExists('repartitions');
    }
};
