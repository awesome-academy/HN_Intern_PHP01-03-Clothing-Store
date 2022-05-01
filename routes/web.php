<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LangController;
use App\Http\Controllers\Store\CartController;
use App\Http\Controllers\Store\CommentController;
use App\Http\Controllers\Store\ProductStoreController;
use App\Http\Controllers\Store\SiteController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::redirect('/', 'home');

Route::get('language/{language}', [LangController::class, 'changeLanguage'])->name('language');

Auth::routes();

Route::prefix('admin')->name('admin.')->middleware('checkAdmin')->group(function () {
    Route::get('/index', [AdminController::class, 'index']);
    Route::resource('products', ProductController::class);
    Route::prefix('users')->name('users.')->controller(UserController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
    });
    Route::resource('categories', CategoryController::class);
});

Route::group(['prefix' => '/'], function () {
    Route::get('/home', [SiteController::class, 'index'])->name('home');
    Route::prefix('product')->name('product.')->group(function () {
        Route::get('/', [ProductStoreController::class, 'shop'])->name('shop');
        Route::get('/search', [ProductStoreController::class, 'filter'])->name('search');
        Route::get('/{slug}.html', [ProductStoreController::class, 'detail'])->name('detail');
        Route::get('/category/{slug}.html', [ProductStoreController::class, 'category'])->name('category');
    });

    Route::prefix('cart')
    ->name('cart.')
    ->middleware('checkLogin')
    ->controller(CartController::class)
    ->group(function () {
        Route::get('/', 'cart')->name('showCart');
        Route::post('/addToCart', 'addToCart')->name('addToCart');
        Route::get('/update/{id}/{qty}', 'update')->name('update');
        Route::get('/delete/{id}', 'delete')->name('delete');
    });

    Route::post('/', [CommentController::class, 'comment'])->name('comment')->middleware('checkLogin');
});
