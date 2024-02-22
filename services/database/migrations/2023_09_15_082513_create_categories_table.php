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
        Schema::create('categories', function (Blueprint $table) {
            $table->char('id', 20)->primary();
            $table->string('designation', 50);
            $table->integer('fixed_amount')->nullable();
            $table->boolean('status')->default(true);
            $table->enum('type', ['FIXE', 'VARIABLE', 'EPARGNE']);
            $table->char('create_id')->nullable();
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
        Schema::dropIfExists('fixed_charges');
    }
};
