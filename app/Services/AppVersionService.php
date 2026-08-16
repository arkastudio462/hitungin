<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class AppVersionService
{
    public function getLatestVersion(): string
    {
        return config('android.latest_version', '1.0.0');
    }

    public function getDownloadUrl(): string
    {
        return config('android.download_url', 'https://github.com/arkastudio462/hitungin/releases/latest');
    }

    public function isUpdateAvailable(string $currentVersion): bool
    {
        return version_compare($currentVersion, $this->getLatestVersion(), '<');
    }

    public function notifyUserIfUpdateAvailable(User $user, string $currentVersion): void
    {
        if (! $this->isUpdateAvailable($currentVersion)) {
            return;
        }

        $latestVersion = $this->getLatestVersion();
        $title = 'Update Tersedia: v'.$latestVersion;
        $message = "Hitungin v{$latestVersion} sudah tersedia. Versi baru hadir dengan perbaikan dan fitur terbaru. Download sekarang!";

        $existingNotification = $user->notifications()
            ->where('type', 'app_update_available')
            ->where('data->latest_version', $latestVersion)
            ->first();

        if ($existingNotification) {
            return;
        }

        $user->notifications()->create([
            'type' => 'app_update_available',
            'title' => $title,
            'message' => $message,
            'data' => [
                'latest_version' => $latestVersion,
                'current_version' => $currentVersion,
                'download_url' => $this->getDownloadUrl(),
            ],
        ]);

        Log::info("Update notification created for user {$user->id}: v{$currentVersion} -> v{$latestVersion}");
    }
}
