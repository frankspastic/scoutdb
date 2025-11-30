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
        Schema::create('scouts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('person_id');
            $table->string('grade', 50)->nullable();
            $table->string('rank', 50)->nullable();
            $table->string('den', 50)->nullable();
            $table->date('registration_expiration_date')->nullable();
            $table->string('registration_status', 50)->nullable();
            $table->string('ypt_status', 50)->nullable();
            $table->string('program', 50)->default('Cub Scouting');
            $table->timestamps();

            // Foreign keys
            $table->foreign('person_id')->references('id')->on('persons')->onDelete('cascade');

            // Indexes
            $table->index('person_id');
            $table->index('registration_expiration_date');
            $table->index('den');
            $table->index('rank');
            $table->index('grade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scouts');
    }
};
