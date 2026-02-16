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
    Schema::create('machines', function (Blueprint $table) {
        $table->id();
        $table->string('internal_id')->unique();
        $table->string('plate')->unique()->nullable();
        $table->foreignId('machine_type_id')->constrained();
        $table->string('brand')->nullable();
        $table->string('model')->nullable();
        $table->string('observations')->nullable();
        $table->string('fuel_type')->nullable();
        $table->integer('fuel_capacity')->nullable();
        $table->softDeletes();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machines');
    }
};
