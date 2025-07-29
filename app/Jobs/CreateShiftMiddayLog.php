<?php

namespace App\Jobs;

use App\Models\ShiftMiddayLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateShiftMiddayLog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $shiftId;
    public $userId;
    public $logTime;
    public $notes;

    /**
     * Create a new job instance.
     */
    public function __construct($shiftId, $userId, $logTime, $notes = null)
    {
        $this->shiftId = $shiftId;
        $this->userId = $userId;
        $this->logTime = $logTime;
        $this->notes = $notes;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $middayLog = ShiftMiddayLog::create([
            'shift_id' => $this->shiftId,
            'user_id' => $this->userId,
            'log_time' => $this->logTime,
            'notes' => $this->notes,
        ]);

        // Send email with the created midday log data
        Mail::raw(
            "ShiftMiddayLog Created:\n" .
            "Shift ID: {$middayLog->shift_id}\n" .
            "User ID: {$middayLog->user_id}\n" .
            "Log Time: {$middayLog->log_time}\n" .
            "Notes: {$middayLog->notes}\n" .
            "Created At: {$middayLog->created_at}",
            function ($message) {
                $message->to('alshahed.cse@gmail.com')
                        ->subject('New Shift Midday Log Created');
            }
        );
    }
}
