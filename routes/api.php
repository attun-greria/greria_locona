<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\MunicipalityController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 公開API（11-1 API方針）
|--------------------------------------------------------------------------
| MVPは公開系の読み取りAPIのみ。認証不要（将来は公開キー導入を検討）。
*/

Route::get('activities', [ActivityController::class, 'index'])->name('api.activities.index');
Route::get('activities/{activity}', [ActivityController::class, 'show'])->name('api.activities.show');

Route::get('municipalities', [MunicipalityController::class, 'index'])->name('api.municipalities.index');
Route::get('municipalities/{municipality}', [MunicipalityController::class, 'show'])->name('api.municipalities.show');
