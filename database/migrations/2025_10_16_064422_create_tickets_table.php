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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            // Ticket details
            $table->string('subject');
            $table->text('description');

            // Relations
            $table->foreignId('client_id')
                  ->constrained('clients')
                  ->onDelete('cascade'); // Delete tickets if client is deleted

            $table->foreignId('assigned_to')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete(); // If user is deleted, set assigned_to to null

            // Ticket status and priority
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])
                  ->default('open');

            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])
                  ->default('medium');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign keys before dropping the table to avoid rollback errors
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropForeign(['assigned_to']);
        });

        Schema::dropIfExists('tickets');
    }
};
