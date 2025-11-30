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
        Schema::create('adult_leaders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('person_id');
            $table->json('positions')->nullable();
            $table->string('ypt_status', 50)->nullable();
            $table->date('ypt_completion_date')->nullable();
            $table->date('ypt_expiration_date')->nullable();
            $table->date('registration_expiration_date')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('person_id')->references('id')->on('persons')->onDelete('cascade');

            // Indexes
            $table->index('person_id');
            $table->index('ypt_expiration_date');
            $table->index('registration_expiration_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adult_leaders');
    }
};
