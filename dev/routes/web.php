<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


	// 奉仕テンプレ管理画面
	Route::get('service_template', 'App\Http\Controllers\ServiceTemplateController@index');
	Route::post('service_template/reg_action', 'App\Http\Controllers\ServiceTemplateController@regAction');
	Route::get('service_template/create', 'App\Http\Controllers\ServiceTemplateController@create');
	Route::post('service_template/store', 'App\Http\Controllers\ServiceTemplateController@store');
	Route::get('service_template/show', 'App\Http\Controllers\ServiceTemplateController@show');
	Route::get('service_template/edit', 'App\Http\Controllers\ServiceTemplateController@edit');
	Route::post('service_template/update', 'App\Http\Controllers\ServiceTemplateController@update');
	Route::post('service_template/auto_save', 'App\Http\Controllers\ServiceTemplateController@auto_save');
	Route::post('service_template/disabled', 'App\Http\Controllers\ServiceTemplateController@disabled');
	Route::post('service_template/destroy', 'App\Http\Controllers\ServiceTemplateController@destroy');
	Route::get('service_template/csv_download', 'App\Http\Controllers\ServiceTemplateController@csv_download');
	Route::post('service_template/ajax_pwms', 'App\Http\Controllers\ServiceTemplateController@ajax_pwms');


	// 奉仕管理画面
	Route::get('service', 'App\Http\Controllers\ServiceController@index');
	Route::post('service/reg_action', 'App\Http\Controllers\ServiceController@regAction');
	Route::get('service/create', 'App\Http\Controllers\ServiceController@create');
	Route::post('service/store', 'App\Http\Controllers\ServiceController@store');
	Route::get('service/show', 'App\Http\Controllers\ServiceController@show');
	Route::get('service/edit', 'App\Http\Controllers\ServiceController@edit');
	Route::post('service/update', 'App\Http\Controllers\ServiceController@update');
	Route::post('service/auto_save', 'App\Http\Controllers\ServiceController@auto_save');
	Route::post('service/disabled', 'App\Http\Controllers\ServiceController@disabled');
	Route::post('service/destroy', 'App\Http\Controllers\ServiceController@destroy');
	Route::get('service/csv_download', 'App\Http\Controllers\ServiceController@csv_download');
	Route::post('service/ajax_pwms', 'App\Http\Controllers\ServiceController@ajax_pwms');

require __DIR__.'/auth.php';
