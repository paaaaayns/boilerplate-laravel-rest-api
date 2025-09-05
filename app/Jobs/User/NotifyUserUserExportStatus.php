<?php

namespace App\Jobs\User;

use App\Models\User;
use App\Notifications\User\ExportUserStatusNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class NotifyUserUserExportStatus implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        protected string $requesterId,
        protected $filename = null,
    ) {}

    public function handle(): void
    {
        $exporter = User::find($this->requesterId);
        $filepath = "exports/users/{$this->filename}";

        if (! Storage::disk('private')->exists($filepath)) {
            $exporter?->notify(new ExportUserStatusNotification([
                'success' => false,
                'message' => 'User export has failed.',
            ]));
        } else {
            $apiURL = config('app.api_url');
            $downloadURL = "{$apiURL}/users/export/download/{$this->filename}";

            $exporter?->notify(new ExportUserStatusNotification([
                'success' => true,
                'message' => 'User export file is ready to download.',
                'data' => [
                    'download_link' => $downloadURL,
                ],
            ]));
        }
    }
}
