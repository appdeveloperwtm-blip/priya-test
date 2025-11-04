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
        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('lead_id')
                  ->constrained('leads')
                  ->onDelete('cascade'); // Delete follow-ups if lead is deleted

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade'); // Delete follow-ups if user is deleted

            // Follow-up details
            $table->text('notes');
            $table->dateTime('follow_up_date');

            // Status: pending or completed
            $table->enum('status', ['pending', 'completed'])->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign keys before dropping the table for safe rollback
        Schema::table('follow_ups', function (Blueprint $table) {
            $table->dropForeign(['lead_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::dropIfExists('follow_ups');
    }
};
