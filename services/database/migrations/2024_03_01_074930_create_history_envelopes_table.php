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
        Schema::create('history_envelopes', function (Blueprint $table) {
            $table->id();
            $table->char('category_id');
            $table->string('type', 10);
            $table->integer('env_amount');
            $table->string('from_budget', 50)->nullable();
            $table->string('to_budget', 50)->nullable();
            $table->char('create_id');
            $table->char('update_id')->nullable();
            $table->timestamps();

            $table->foreign('create_id')->references('id')->on('users');
            $table->foreign('update_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('history_envelopes');
    }
};
