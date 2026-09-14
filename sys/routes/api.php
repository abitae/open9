<?php

use App\Http\Controllers\Api\Account\AddressController;
use App\Http\Controllers\Api\Account\OrderController;
use App\Http\Controllers\Api\Account\ProfileController;
use App\Http\Controllers\Api\Auth\ClientAuthController;
use App\Http\Controllers\Api\Auth\GoogleAuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\NewsletterController;
use App\Http\Controllers\Api\SiteController;
use Illuminate\Support\Facades\Route;

Route::get('/site', [SiteController::class, 'show']);
Route::get('/home', [SiteController::class, 'home']);
Route::get('/legal/{slug}', [SiteController::class, 'legal']);

Route::get('/blog', [ContentController::class, 'blog']);
Route::get('/blog-categories', [ContentController::class, 'blogCategories']);
Route::get('/blog/{slug}', [ContentController::class, 'blogShow']);
Route::get('/projects', [ContentController::class, 'projects']);
Route::get('/project-categories', [ContentController::class, 'projectCategories']);
Route::get('/projects/{slug}', [ContentController::class, 'projectShow']);
Route::get('/services', [ContentController::class, 'services']);
Route::get('/products', [ContentController::class, 'products']);
Route::get('/product-brands', [ContentController::class, 'productBrands']);
Route::get('/product-categories', [ContentController::class, 'productCategories']);
Route::get('/products/{slug}', [ContentController::class, 'productShow']);

Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:10,1');
Route::post('/newsletter/subscribe', [NewsletterController::class, 'store'])->middleware('throttle:10,1');
Route::post('/chat', [ChatController::class, 'store'])->middleware('throttle:20,1');

Route::post('/checkout', [CheckoutController::class, 'store'])->middleware(['throttle:15,1', 'client.verified']);
Route::post('/checkout/process', [CheckoutController::class, 'process'])->middleware(['throttle:15,1', 'client.verified']);
Route::get('/orders/{orderCode}', [CheckoutController::class, 'show'])->middleware('throttle:60,1');
Route::post('/webhooks/mercadopago', [CheckoutController::class, 'webhook']);

Route::prefix('auth')->group(function () {
    Route::post('/register', [ClientAuthController::class, 'register'])->middleware('throttle:10,1');
    Route::post('/login', [ClientAuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('/verify-email', [ClientAuthController::class, 'verifyEmail'])->middleware('throttle:10,1');
    Route::post('/resend-verification', [ClientAuthController::class, 'resendVerification'])->middleware('throttle:5,1');

    Route::get('/google/redirect', [GoogleAuthController::class, 'redirect'])->middleware('throttle:20,1');
    Route::get('/google/callback', [GoogleAuthController::class, 'callback'])->middleware('throttle:20,1');

    Route::middleware(['auth:sanctum', 'client.active'])->group(function () {
        Route::post('/logout', [ClientAuthController::class, 'logout']);
    });

    Route::middleware(['auth:sanctum', 'client.active', 'client.verified'])->group(function () {
        Route::get('/me', [ClientAuthController::class, 'me']);
    });
});

Route::middleware(['auth:sanctum', 'client.active', 'client.verified'])->prefix('account')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);

    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::put('/addresses/{address}', [AddressController::class, 'update']);
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy']);
    Route::post('/addresses/{address}/default', [AddressController::class, 'setDefault']);

    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{orderCode}', [OrderController::class, 'show']);
});
