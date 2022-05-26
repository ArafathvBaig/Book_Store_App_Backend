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

/**
 * @OA\Post(
 *  path="/api/addrating",
 *  summary="Give Rating for a Book",
 *  description="Give Rating for a Book You Ordered",
 *  @OA\RequestBody(
 *  	@OA\JsonContent(),
 *      @OA\MediaType(
 *      	mediaType="multipart/form-data",
 *          @OA\Schema(
 *          	type="object",
 *              required={"user_rating","order_id"},
 *              @OA\Property(property="user_rating", type="integer"),
 *              @OA\Property(property="order_id", type="integer"),
 *        	),
 *    	),
 *	),
 *  @OA\Response(response=201, description="Rating Added Successfully"),
 *  @OA\Response(response=401, description="Invalid Authorization Token"),
 *  @OA\Response(response=404, description="Order Not Found"),
 *  @OA\Response(response=409, description="You Already Gave Rating For Your Order"),
 *  @OA\Response(response=406, description="Rating Should be +ve and less than 5"),
 *  security = {
 *		{ "Bearer" : {} }
 *  }
 * )
 * 
 * Function to Add Rating for the Book Purchased,
 * take the User_rating and order_id,
 * validate the user authentication token and
 * if valid credentials and authenticated user,
 * Give the Rating for the Order you Placed.
 * 
 * @return \Illuminate\Http\JsonResponse
 */
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
                                    $rating = Rating::addRating($request, $currentUser->id, $book);
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
}
