<?php

namespace App\Jobs;

use App\Events\MessageReceived;
use App\Models\Message;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessMessage implements ShouldQueue
{
    use Queueable;

    public Message $message;

    /**
     * Create a new job instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Sanitize the message
        $sanitizedMessage = strip_tags($this->message->message);
        $sanitizedMessage = htmlspecialchars($sanitizedMessage, ENT_QUOTES, 'UTF-8');

        // Append metadata
        $processedMessage = $sanitizedMessage . ' [Processed at: ' . now()->toDateTimeString() . ']';

        // Update the message
        $this->message->update([
            'processed_message' => $processedMessage,
            'is_processed' => true,
        ]);

        // Broadcast the message to all connected clients
        broadcast(new MessageReceived($this->message->fresh()));
    }
}
