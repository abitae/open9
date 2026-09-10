<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SiteConfigService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function __construct(
        private readonly SiteConfigService $siteConfig,
    ) {}

    public function blog(): JsonResponse
    {
        return response()->json(['data' => $this->siteConfig->blogPosts()]);
    }

    public function blogShow(string $slug): JsonResponse
    {
        $post = $this->siteConfig->blogPost($slug);

        if ($post === null) {
            return response()->json(['message' => 'Post no encontrado.'], 404);
        }

        return response()->json($post);
    }

    public function projects(): JsonResponse
    {
        return response()->json(['data' => $this->siteConfig->projects()]);
    }

    public function projectShow(string $slug): JsonResponse
    {
        $project = $this->siteConfig->project($slug);

        if ($project === null) {
            return response()->json(['message' => 'Proyecto no encontrado.'], 404);
        }

        return response()->json($project);
    }

    public function services(): JsonResponse
    {
        return response()->json(['data' => $this->siteConfig->services()]);
    }

    public function products(Request $request): JsonResponse
    {
        $brand = $request->string('brand')->trim()->toString();

        return response()->json([
            'data' => $this->siteConfig->products($brand !== '' ? $brand : null),
        ]);
    }

    public function productBrands(): JsonResponse
    {
        return response()->json(['data' => $this->siteConfig->productBrands()]);
    }

    public function productShow(string $slug): JsonResponse
    {
        $product = $this->siteConfig->product($slug);

        if ($product === null) {
            return response()->json(['message' => 'Producto no encontrado.'], 404);
        }

        return response()->json($product);
    }
}
