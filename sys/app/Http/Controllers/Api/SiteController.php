<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SiteConfigService;
use Illuminate\Http\JsonResponse;

class SiteController extends Controller
{
    public function __construct(
        private readonly SiteConfigService $siteConfig,
    ) {}

    public function show(): JsonResponse
    {
        return response()->json($this->siteConfig->sitePayload());
    }

    public function home(): JsonResponse
    {
        return response()->json($this->siteConfig->homePayload());
    }

    public function legal(string $slug): JsonResponse
    {
        $page = $this->siteConfig->legalPage($slug);

        if ($page === null) {
            return response()->json(['message' => 'Página no encontrada.'], 404);
        }

        return response()->json($page);
    }
}
