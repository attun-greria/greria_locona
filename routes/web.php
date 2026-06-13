<?php

use App\Http\Controllers\Admin\ActivityController as AdminActivityController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExtractionReviewController;
use App\Http\Controllers\Admin\MunicipalityController as AdminMunicipalityController;
use App\Http\Controllers\Admin\ReviewQueueController;
use App\Http\Controllers\Admin\SourceController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Public\ActivityController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\MunicipalityController;
use App\Http\Controllers\Public\OutboundController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 利用者向け公開Web（PUB-xxx）
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');
Route::get('/activities/{activity}', [ActivityController::class, 'show'])->name('activities.show');
// 一次情報への送客＋クリック計測（PUB-006）
Route::get('/go/{activity}', [OutboundController::class, 'redirect'])->name('outbound.redirect');

Route::get('/municipalities', [MunicipalityController::class, 'index'])->name('municipalities.index');
Route::get('/municipalities/{municipality}', [MunicipalityController::class, 'show'])->name('municipalities.show');

/*
|--------------------------------------------------------------------------
| 管理画面（ADM-xxx）
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    // 認証
    Route::get('login', [LoginController::class, 'show'])->middleware('guest')->name('login');
    Route::post('login', [LoginController::class, 'login'])->middleware('guest');
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    // 認証必須エリア（operator 以上）
    Route::middleware('admin.access')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('municipalities', AdminMunicipalityController::class)->except('show');
        Route::resource('sources', SourceController::class)->except('show');

        // 活動
        Route::resource('activities', AdminActivityController::class)->except('show');
        Route::post('activities/{activity}/duplicate', [AdminActivityController::class, 'duplicate'])->name('activities.duplicate');
        Route::patch('activities/{activity}/status', [AdminActivityController::class, 'changeStatus'])->name('activities.status');

        // カテゴリ・タグ
        Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('tags', [TagController::class, 'index'])->name('tags.index');
        Route::post('tags', [TagController::class, 'store'])->name('tags.store');
        Route::delete('tags/{tag}', [TagController::class, 'destroy'])->name('tags.destroy');

        // 抽出レビュー・確認キュー
        Route::get('extractions', [ExtractionReviewController::class, 'index'])->name('extractions.index');
        Route::get('extractions/{extraction}', [ExtractionReviewController::class, 'show'])->name('extractions.show');
        Route::post('extractions/{extraction}/review', [ExtractionReviewController::class, 'review'])->name('extractions.review');
        Route::get('review', [ReviewQueueController::class, 'index'])->name('review.index');

        // 監査ログ（admin のみ）
        Route::get('audit-logs', [AuditLogController::class, 'index'])
            ->middleware('admin.access:admin')->name('audit-logs.index');
    });
});
