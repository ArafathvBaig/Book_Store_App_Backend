<?php

namespace App\Http\Controllers;

use App\Exceptions\BookStoreException;
use App\Notifications\SendOrderDetails;
use App\Notifications\SendCancelOrderDetails;
use App\Models\User;
use App\Models\Book;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Mail\Mailer;
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

class OrderController extends Controller
{
	/**
	 * @OA\Post(
	 *  path="/api/placeorder",
	 *  summary="Place  Order",
	 *  description="Place a Order",
	 *  @OA\RequestBody(
	 *  	@OA\JsonContent(),
	 *      @OA\MediaType(
	 *      	mediaType="multipart/form-data",
	 *          @OA\Schema(
	 *          	type="object",
	 *              required={"address_id","cart_id"},
	 *              @OA\Property(property="address_id", type="integer"),
	 *              @OA\Property(property="cart_id", type="integer"),
	 *        	),
	 *    	),
	 *	),
	 *  @OA\Response(response=201, description="Order Placed Successfully"),
	 *  @OA\Response(response=404, description="Address Not Found"),
	 *  @OA\Response(response=401, description="Invalid Authorization Token"),
	 *  @OA\Response(response=409, description="Already Placed an Order"),
	 *  @OA\Response(response=406, description="Book Stock is Not Available in The Store"),
	 *  security = {
	 *		{ "Bearer" : {} }
	 *  }
	 * )
	 * 
	 * Function to place an order of an user,
	 * take the cart_id and address_id,
	 * validate the user authentication token and
	 * if valid credentials and authenticated user,
	 * place the order successfully and send Order Details to Mail.
	 * 
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function placeOrder(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
				'cart_id' => 'required|integer',
				'address_id' => 'required|integer'
			]);

			if ($validator->fails()) {
				return response()->json($validator->errors()->toJson(), 400);
			}

			$currentUser = JWTAuth::parseToken()->authenticate();
			if ($currentUser) {
				$user = User::checkUser($currentUser->id);
				if ($user) {
					$order = Order::getOrder($request->cart_id);
					if (!$order) {
						$cart = Cart::getCartByIdandUserId($request->cart_id, $currentUser->id);
						if ($cart) {
							$book = Book::getBookById($cart->book_id);
							if ($book) {
								if ($cart->book_quantity <= $book->quantity) {
									$address = Address::getUserAddress($request->address_id, $currentUser->id);
									if ($address) {
										$order = Order::placeOrder($request, $currentUser, $book, $cart);
										if ($order) {
											$book->quantity  -= $cart->book_quantity;
											$book->save();

											$delay = now()->addSeconds(600);
											$currentUser->notify((new SendOrderDetails($order, $book, $cart, $currentUser))->delay($delay));
											
											// $mail = new Mailer();
											// $check = $mail->sendOrderDetails($order, $book, $cart, $currentUser);

											Log::info('Order Placed Successfully');
											Cache::remember('orders', 3600, function () {
												return DB::table('orders')->get();
											});

											return response()->json([
												'message' => 'Order Placed Successfully',
												'OrderId' => $order->order_id,
												'Quantity' => $cart->book_quantity,
												'Total_Price' => $order->total_price,
												'Message' => 'Mail Sent to Users Mail With Order Details',
											], 201);
										}
									}
									Log::error('Address Not Found');
									throw new BookStoreException('Address Not Found', 404);
								}
								Log::error('Book Stock is Not Available in The Store');
								throw new BookStoreException('Book Stock is Not Available in The Store', 406);
							}
							Log::error('Book Not Found in Store');
							throw new BookStoreException('Book Not Found in Store', 404);
						}
						Log::error('Cart Not Found');
						throw new BookStoreException('Cart Not Found', 404);
					}
					Log::error('Already Placed an Order');
					throw new BookStoreException('Already Placed an Order', 409);
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

	/**
	 * @OA\Post(
	 *  path="/api/cancelorder",
	 *  summary="Cancel  Order",
	 *  description="Cancel a Order",
	 *  @OA\RequestBody(
	 *  	@OA\JsonContent(),
	 *      @OA\MediaType(
	 *      	mediaType="multipart/form-data",
	 *          @OA\Schema(
	 *          	type="object",
	 *              required={"order_id"},
	 *              @OA\Property(property="order_id", type="string"),
	 *        	),
	 *    	),
	 *	),
	 *  @OA\Response(response=200, description="Order Cancelled Successfully"),
	 *  @OA\Response(response=404, description="Order Not Found"),
	 *  @OA\Response(response=401, description="Invalid Authorization Token"),
	 *  @OA\Response(response=406, description="Invalid OrderID"),
	 *  security = {
	 *		{ "Bearer" : {} }
	 *  }
	 * )
	 * 
	 * Function to Cancel an Order placed by User,
	 * validate the Order_id and user Authentication Token,
	 * if valid user and order_id is valid,
	 * Cancel the order Successfully
	 * 
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function cancelOrder(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
				'order_id' => 'required|string'
			]);

			if ($validator->fails()) {
				return response()->json($validator->errors()->toJson(), 400);
			}

			$currentUser = JWTAuth::parseToken()->authenticate();
			if ($currentUser) {
				$user = User::checkUser($currentUser->id);
				if ($user) {
					if (strlen($request->order_id) == 9) {
						$order = Order::getOrderByOrderID($request->order_id, $currentUser->id);
						if ($order) {
							$cart = Cart::getCartByIdandUserId($order->cart_id, $currentUser->id);
							$book = Book::getBookById($cart->book_id);
							if ($order->delete()) {
								$book->quantity += $cart->book_quantity;
								$book->save();

								$delay = now()->addSeconds(600);
								$user->notify((new SendCancelOrderDetails($order, $book, $cart, $currentUser))->delay($delay));

								// $mail = new Mailer();
								// $check = $mail->sendOrderCancelDetails($order, $book, $cart, $currentUser);

								Log::info('Order Cancelled Successfully');
								Cache::forget('orders');

								return response()->json([
									'message' => 'Order Cancelled Successfully',
									'OrderId' => $order->order_id,
									'Quantity' => $cart->book_quantity,
									'Total_Price' => $order->total_price,
									'Message' => 'Mail Sent to Users Mail With Order Details'
								], 200);
							}
						}
						Log::error('Order Not Found');
						throw new BookStoreException('Order Not Found', 404);
					}
					Log::error('Invalid OrderID');
					throw new BookStoreException('Invalid OrderID', 406);
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
