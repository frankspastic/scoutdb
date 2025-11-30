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
        Schema::create('persons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('family_id')->nullable();
            $table->string('bsa_member_id', 20)->nullable();
            $table->enum('person_type', ['scout', 'parent', 'sibling', 'adult_leader']);
            $table->string('prefix', 10)->nullable();
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);
            $table->string('suffix', 10)->nullable();
            $table->string('nickname', 100)->nullable();
            $table->enum('gender', ['M', 'F', 'Other'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->integer('age')->nullable();
            $table->string('email', 255)->nullable();
            $table->string('phone', 20)->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('family_id')->references('id')->on('families')->onDelete('set null');

            // Indexes
            $table->index('family_id');
            $table->index('bsa_member_id');
            $table->index('email');
            $table->index(['last_name', 'first_name']);
            $table->index('person_type');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('persons');
    }
};
