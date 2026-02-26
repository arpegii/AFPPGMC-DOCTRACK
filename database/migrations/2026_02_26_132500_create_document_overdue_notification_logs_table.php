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
        Schema::create('document_overdue_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('receiving_unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->timestamp('notified_at');
            $table->timestamps();

            $table->unique(['document_id', 'user_id', 'receiving_unit_id'], 'doc_overdue_notify_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_overdue_notification_logs');
    }
};

