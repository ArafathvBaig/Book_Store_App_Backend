<?php

namespace App\Http\Controllers;

use App\Exceptions\BookStoreException;
use App\Models\User;
use App\Models\Book;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Feedback;
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

class FeedbackController extends Controller
{
    public function addFeedback(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_feedback' => 'required|string|between:5,1000'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 400);
            }

            $currentUser = JWTAuth::parseToken()->authenticate();
            if ($currentUser) {
                $user = User::checkUser($currentUser->id);
                if ($user) {
                    $orders = Order::getOrderByUserId($currentUser->id);
                    if ($orders) {
                        $feedback = Feedback::getFeedback($currentUser->id);
                        if(!$feedback){
                            $feedback = Feedback::addFeedback($request, $currentUser->id);
                            if ($feedback) {
                                Log::info('FeedBack Added Successfully. Thank You For Your FeedBack.');
                                return response()->json([
                                    'message' => 'FeedBack Added Successfully. Thank You For Your FeedBack.'
                                ], 201);
                            }
                        }
                        Log::error('You Have Already Given Us a FeedBack. Thank You For Your FeedBack.');
                        throw new BookStoreException('You Have Already Given Us a FeedBack. Thank You For Your FeedBack.', 409);
                    }
                    Log::error('Orders Not Found. First Make an Order and Give Feedback');
                    throw new BookStoreException('Orders Not Found. First Make an Order and Give Feedback', 404);
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
