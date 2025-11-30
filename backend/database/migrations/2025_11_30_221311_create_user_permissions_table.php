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
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wordpress_user_id');
            $table->unsignedBigInteger('person_id')->nullable();
            $table->enum('role', ['admin', 'editor', 'viewer']);
            $table->unsignedBigInteger('granted_by')->nullable();
            $table->timestamp('granted_at')->useCurrent();
            $table->timestamps();

            // Foreign keys
            $table->foreign('person_id')->references('id')->on('persons')->onDelete('set null');

            // Indexes
            $table->unique('wordpress_user_id');
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
    }
};
