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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();

            // Lead details
            $table->string('title');

            // Relations
            $table->foreignId('client_id')
                  ->constrained('clients')
                  ->onDelete('cascade'); // If a client is deleted, their leads are deleted

            $table->foreignId('assigned_to')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete(); // If a user is deleted, set assigned_to = null

            // Lead status
            $table->enum('status', [
                'new',
                'contacted',
                'qualified',
                'converted',
                'lost'
            ])->default('new');

            // Optional value + notes
            $table->decimal('value', 10, 2)->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign keys first to avoid rollback errors
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropForeign(['assigned_to']);
        });

        Schema::dropIfExists('leads');
    }
};
