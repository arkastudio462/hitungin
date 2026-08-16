<?php

namespace App\Http\Controllers;

use App\Services\AppVersionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppVersionController extends Controller
{
    public function __construct(
        private AppVersionService $versionService,
    ) {}

    public function check(Request $request): JsonResponse
    {
        $request->validate([
            'current_version' => 'required|string',
        ]);

        $latestVersion = $this->versionService->getLatestVersion();
        $downloadUrl = $this->versionService->getDownloadUrl();
        $isUpdateAvailable = $this->versionService->isUpdateAvailable($request->current_version);

        if ($isUpdateAvailable && $request->user()) {
            $this->versionService->notifyUserIfUpdateAvailable(
                $request->user(),
                $request->current_version
            );
        }

        return response()->json([
            'latest_version' => $latestVersion,
            'download_url' => $downloadUrl,
            'update_available' => $isUpdateAvailable,
        ]);
    }
}
