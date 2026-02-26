<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('document_overdue_notification_logs', 'notification_date')) {
            Schema::table('document_overdue_notification_logs', function (Blueprint $table) {
                $table->date('notification_date')->nullable()->after('notified_at');
            });
        }

        DB::table('document_overdue_notification_logs')
            ->whereNull('notification_date')
            ->update(['notification_date' => DB::raw('DATE(notified_at)')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('document_overdue_notification_logs', 'notification_date')) {
            Schema::table('document_overdue_notification_logs', function (Blueprint $table) {
                $table->dropColumn('notification_date');
            });
        }
    }
};
