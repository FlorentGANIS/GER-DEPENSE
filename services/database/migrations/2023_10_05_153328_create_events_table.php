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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->char('budget_id');
            $table->string('title', 100);
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->char('create_id')->nullable();
            $table->char('update_id')->nullable();
            $table->timestamps();

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
        Schema::dropIfExists('events');
    }
};
