<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatStreamed implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public int $userId;
    public string $chunk;
    public bool $done;
    public ?string $error;

    public function __construct(int $userId, string $chunk, bool $done = false, ?string $error = null)
    {
        $this->userId = $userId;
        $this->chunk = $chunk;
        $this->done = $done;
        $this->error = $error;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('chat.' . $this->userId);
    }

    public function broadcastAs(): string
    {
        return 'chat.stream';
    }

    public function broadcastWith(): array
    {
        return [
            'chunk' => $this->chunk,
            'done' => $this->done,
            'error' => $this->error,
        ];
    }
}
