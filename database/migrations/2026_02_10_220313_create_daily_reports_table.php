<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnDelete();

            $table->foreignId('machine_id')
                ->constrained('machines')
                ->cascadeOnDelete();

            $table->date('date');

            $table->decimal('initial_km', 10, 2)->nullable();
            $table->decimal('final_km', 10, 2)->nullable();

            $table->decimal('initial_hm', 10, 2)->nullable();
            $table->decimal('final_hm', 10, 2)->nullable();

            $table->text('work_description')->nullable();

            $table->decimal('fuel_quantity', 10, 2)->nullable();
            $table->text('fuel_observation')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};
