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
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 255);
            $table->string('category', 50)->default('soporte_tecnico');
            $table->text('description')->nullable();
            $table->string('status', 30)->default('pending');
            $table->text('final_comment')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'category']);
            $table->index(['user_id', 'status']);
            $table->index(['assigned_to_user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
