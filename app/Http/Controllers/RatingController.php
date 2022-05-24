<?php

namespace App\Http\Controllers;

use App\Exceptions\BookStoreException;
use App\Models\User;
use App\Models\Book;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

class RatingController extends Controller
{
    public function addRating(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_rating' => 'required|integer',
                'order_id' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 400);
            }

            $currentUser = JWTAuth::parseToken()->authenticate();
            if ($currentUser) {
                $user = User::checkUser($currentUser->id);
                if ($user) {
                    $order = Order::getOrderByIDandUserID($request->order_id, $currentUser->id);
                    if ($order) {
                        $book = Book::getBookByName($order->book_name);
                        if ($book) {
                            $rated = Rating::getRating($request->order_id, $currentUser->id, $book->id);
                            if (!$rated) {
                                if ($request->user_rating >= 0 && $request->user_rating <= 5) {
                                    $rating = Rating::addRating($request, $currentUser->id, $book->id);
                                    if ($rating) {
                                        Log::info('Rating Added Successfully');
                                        return response()->json([
                                            'message' => 'Rating Added Successfully'
                                        ], 201);
                                    }
                                }
                                Log::error('Rating Should be +ve and less than 5');
                                throw new BookStoreException('Rating Should be +ve and less than 5', 406);
                            }
                            Log::error('You Already Gave Rating For Your Order');
                            throw new BookStoreException('You Already Gave Rating For Your Order', 409);
                        }
                        Log::error('Book Not Found');
                        throw new BookStoreException('Book Not Found', 404);
                    }
                    Log::error('Order Not Found');
                    throw new BookStoreException('Order Not Found', 404);
                }
                Log::error('You are Not a User');
                throw new BookStoreException('You are Not a User', 404);
            }
            Log::error('Invalid Authorization Token');
            throw new BookStoreException('Invalid Authorization Token', 401);
        } catch (BookStoreException $exception) {
            return response()->json([
                'message' => $exception->message()
            ], $exception->statusCode());
        }
    }

    public function getAverageRating(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'book_id' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 400);
            }

            $currentUser = JWTAuth::parseToken()->authenticate();
            if ($currentUser) {
                $user = User::checkUser($currentUser->id);
                if ($user) {
                    $book = Book::getBookById($request->book_id);
                    if ($book) {
                        $rating = Rating::getAverageRating($request->book_id);
                        if ($rating) {
                            Log::info('We Have Ratings For the Book');
                            return response()->json([
                                'message' => 'Average Rating of The Book::',
                                'Average Rating' => $rating
                            ], 200);
                        }
                        Log::error('Ratings Not Yet Given');
                        throw new BookStoreException('Ratings Not Yet Given', 404);
                    }
                    Log::error('Book Not Found');
                    throw new BookStoreException('Book Not Found', 404);
                }
                Log::error('You are Not a User');
                throw new BookStoreException('You are Not a User', 404);
            }
            Log::error('Invalid Authorization Token');
            throw new BookStoreException('Invalid Authorization Token', 401);
        } catch (BookStoreException $exception) {
            return response()->json([
                'message' => $exception->message()
            ], $exception->statusCode());
        }
    }
}
