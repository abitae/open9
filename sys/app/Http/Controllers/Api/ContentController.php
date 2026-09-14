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

    public function blog(Request $request): JsonResponse
    {
        $search = $request->string('search')->trim()->toString();
        $category = $request->string('category')->trim()->toString();
        $page = max(1, $request->integer('page', 1));

        return response()->json(
            $this->siteConfig->blogPosts(
                $search !== '' ? $search : null,
                $category !== '' ? $category : null,
                $page
            )
        );
    }

    public function blogCategories(): JsonResponse
    {
        return response()->json(['data' => $this->siteConfig->blogCategories()]);
    }

    public function blogShow(string $slug): JsonResponse
    {
        $post = $this->siteConfig->blogPost($slug);

        if ($post === null) {
            return response()->json(['message' => 'Post no encontrado.'], 404);
        }

        return response()->json($post);
    }

    public function projects(Request $request): JsonResponse
    {
        $category = $request->string('category')->trim()->toString();
        $search = $request->string('search')->trim()->toString();
        $page = max(1, $request->integer('page', 1));
        $perPage = min(50, max(1, $request->integer('per_page', 9)));

        return response()->json($this->siteConfig->projects(
            categorySlug: $category !== '' ? $category : null,
            search: $search !== '' ? $search : null,
            page: $page,
            perPage: $perPage,
        ));
    }

    public function projectCategories(): JsonResponse
    {
        return response()->json(['data' => $this->siteConfig->projectCategories()]);
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
        $ids = $this->requestedProductIds($request);

        if ($ids !== []) {
            return response()->json($this->siteConfig->productsByIds($ids));
        }

        $brand = $request->string('brand')->trim()->toString();
        $category = $request->string('category')->trim()->toString();
        $search = $request->string('search')->trim()->toString();
        $sort = $request->string('sort')->trim()->toString();
        $page = max(1, $request->integer('page', 1));

        return response()->json($this->siteConfig->products(
            brandSlug: $brand !== '' ? $brand : null,
            categorySlug: $category !== '' ? $category : null,
            search: $search !== '' ? $search : null,
            sort: $sort !== '' ? $sort : null,
            inStockOnly: $request->boolean('in_stock'),
            page: $page,
        ));
    }

    /**
     * @return list<int>
     */
    private function requestedProductIds(Request $request): array
    {
        $raw = $request->input('ids', []);

        if (is_string($raw)) {
            $raw = explode(',', $raw);
        }

        if (! is_array($raw)) {
            return [];
        }

        return collect($raw)
            ->map(fn (mixed $id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->take(50)
            ->values()
            ->all();
    }

    public function productBrands(): JsonResponse
    {
        return response()->json(['data' => $this->siteConfig->productBrands()]);
    }

    public function productCategories(): JsonResponse
    {
        return response()->json(['data' => $this->siteConfig->productCategories()]);
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
