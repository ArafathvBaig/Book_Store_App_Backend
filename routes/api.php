<?php

use App\Http\Controllers\BookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Password;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['middleware' => 'api'], function () {
    Route::post('register', [UserController::class, 'register']);
    Route::post('login', [UserController::class, 'login']);
    Route::post('logout', [UserController::class, 'logout']);

    Route::post('forgotpassword', [ForgotPasswordController::class, 'forgotPassword']);
    Route::post('resetpassword', [ForgotPasswordController::class, 'resetPassword']);

    Route::post('addbook', [BookController::class, 'addBook']);
    Route::post('updatebook', [BookController::class, 'updateBookByBookId']);
    Route::post('addquantity', [BookController::class, 'addQuantityToExistingBook']);
    Route::post('deletebook', [BookController::class, 'deleteBookByBookId']);
    Route::get('getallbooks', [BookController::class, 'getAllBooks']);
    Route::post('searchbookbykey', [BookController::class, 'searchBookByKey']);
    Route::get('sortbookbypricelowtohigh', [BookController::class, 'sortBookByPriceLowToHigh']);
    Route::get('sortbookbypricehightolow', [BookController::class, 'sortBookByPriceHighToLow']);


    Route::post('addbooktocart', [CartController::class, 'addBookToCart']);
    Route::post('updatecart', [CartController::class, 'updateCartById']);
    Route::post('deletecart', [CartController::class, 'deleteCartById']);
    Route::get('getallcartbooksofuser', [CartController::class, 'getAllCartBooksOfUser']);

    Route::post('adduseraddress', [AddressController::class, 'addUserAddress']);
    Route::post('updateuseraddress', [AddressController::class, 'updateAddress']);
    Route::post('deleteuseraddress', [AddressController::class, 'deleteAddress']);
    Route::get('getuseraddresses', [AddressController::class, 'getAddress']);

    Route::post('placeorder', [OrderController::class, 'placeOrder']);
    Route::post('cancelorder', [OrderController::class, 'cancelOrder']);

    Route::post('addfeedback', [FeedbackController::class, 'addFeedback']);

    Route::post('addrating', [RatingController::class, 'addRating']);
});

Route::group(['middleware' => ['jwt.verify']], function () {
    Route::get('getUser', [UserController::class, 'get_user']);
});
