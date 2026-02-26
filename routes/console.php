<?php

use App\Models\Document;
use App\Models\User;
use App\Notifications\DocumentOverdueNotification;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('documents:notify-overdue', function () {
    $cutoff = now()->subDays(3);
    $today = now()->toDateString();

    $overdueDocuments = Document::with(['senderUnit', 'receivingUnit'])
        ->where('status', 'incoming')
        ->where(function ($query) use ($cutoff) {
            $query->where(function ($subQuery) use ($cutoff) {
                $subQuery->whereNotNull('forwarded_at')
                    ->where('forwarded_at', '<=', $cutoff);
            })->orWhere(function ($subQuery) use ($cutoff) {
                $subQuery->whereNull('forwarded_at')
                    ->where('created_at', '<=', $cutoff);
            });
        })
        ->get();

    $sentCount = 0;

    foreach ($overdueDocuments as $document) {
        $unitUsers = User::where('unit_id', $document->receiving_unit_id)->get();

        foreach ($unitUsers as $user) {
            $logQuery = DB::table('document_overdue_notification_logs')
                ->where('document_id', $document->id)
                ->where('user_id', $user->id)
                ->where('receiving_unit_id', $document->receiving_unit_id);

            $existingLog = $logQuery->first();
            $alreadySentToday = $existingLog && \Illuminate\Support\Carbon::parse($existingLog->notified_at)->toDateString() === $today;

            if ($alreadySentToday) {
                continue;
            }

            $dispatchedAt = now();

            if ($existingLog) {
                $logQuery->update([
                    'notified_at' => $dispatchedAt,
                    'updated_at' => $dispatchedAt,
                ]);
            } else {
                DB::table('document_overdue_notification_logs')->insert([
                    'document_id' => $document->id,
                    'user_id' => $user->id,
                    'receiving_unit_id' => $document->receiving_unit_id,
                    'notified_at' => $dispatchedAt,
                    'created_at' => $dispatchedAt,
                    'updated_at' => $dispatchedAt,
                ]);
            }

            try {
                $user->notify(new DocumentOverdueNotification($document));
                $sentCount++;
            } catch (\Throwable $e) {
                // Allow retry on next scheduler run if dispatch fails now.
                if ($existingLog) {
                    $logQuery->update([
                        'notified_at' => $existingLog->notified_at,
                        'updated_at' => now(),
                    ]);
                } else {
                    $logQuery->delete();
                }

                throw $e;
            }
        }
    }

    $this->info("Overdue notifications sent: {$sentCount}");
})->purpose('Notify unit users of incoming documents overdue for 3+ days');

Schedule::command('documents:notify-overdue')->everyMinute()->withoutOverlapping();
