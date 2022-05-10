<?php

namespace App\Http\Controllers;

use App\Exceptions\BookStoreException;
use App\Models\Book;
use App\Models\User;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

use function PHPUnit\Framework\isEmpty;

class CartController extends Controller
{
    /**
     * @OA\Post(
     *   path="/api/addbooktocart",
     *   summary="Add Book to cart",
     *   description="User Can Add Book to cart ",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"book_id"},
     *               @OA\Property(property="book_id", type="integer"),
     *               @OA\Property(property="book_quantity", type="integer"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Book Added to Cart Successfully"),
     *   @OA\Response(response=102, description="Book Not Added to Cart"),
     *   @OA\Response(response=401, description="Invalid Authorization Token"),
     *   @OA\Response(response=404, description="Book Not Found"),
     *   @OA\Response(response=406, description="Quantity Cannot be < 0 or > Quantity in Store"),
     *   @OA\Response(response=409, description="Book Already In Cart"),
     *   security = {
     *      { "Bearer" : {} }
     *   }
     * )
     *
     * Function to add a book to the cart
     * Getting the book id and book quantity from user and
     * authenticating the user, if authenticated and 
     * user given valid credentials, add book to cart successfully
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function addBookToCart(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'book_id' => 'required|integer',
                'book_quantity' => ''
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors()->tojson(), 400);
            }

            $currentUser = JWTAuth::parseToken()->authenticate();
            if ($currentUser) {
                $user = User::checkUser($currentUser->id);
                if ($user) {
                    $bookData = Book::getBookById($request->book_id);
                    if ($bookData) {
                        if (($request->book_quantity > 0 && $request->book_quantity <= $bookData->quantity) 
                        || $request->book_quantity == '') {
                            $book = Cart::getCartedBook($request->book_id, $currentUser->id);
                            if (!$book) {
                                $cart = Cart::addBookToYourCart($request, $currentUser);
                                if ($cart) {
                                    Cache::remember('carts', 3600, function () {
                                        return DB::table('carts')->get();
                                    });
                                    Log::info('Book Added to Cart Successfully');
                                    return response()->json([
                                        'message' => 'Book Added to Cart Successfully'
                                    ], 201);
                                }
                                Log::error('Book Not Added to Cart');
                                throw new BookStoreException('Book Not Added to Cart', 102);
                            }
                            Log::error('Book Already In Cart');
                            throw new BookStoreException('Book Already In Cart', 409);
                        }
                        Log::error('Quantity Cannot be < 0 or > Quantity in Store');
                        throw new BookStoreException('Quantity Cannot be < 0 or > Quantity in Store', 406);
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

    /**
     * @OA\Post(
     *   path="/api/updatecart",
     *   summary="update the quantity in cart",
     *   description=" Update cart ",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id", "book_quantity"},
     *               @OA\Property(property="id", type="integer"),
     *               @OA\Property(property="book_quantity", type="integer"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Cart Updated Successfully"),
     *   @OA\Response(response=102, description="Cart Not Updated"),
     *   @OA\Response(response=401, description="Invalid Authorization Token"),
     *   @OA\Response(response=406, description="Quantity Cannot be < 0 or > Quantity in Store"),
     *   @OA\Response(response=404, description="Cart Not Found"),
     *   security = {
     *      { "Bearer" : {} }
     *   }
     * )
     * 
     * Function to update the book inside the cart,
     * using cart id and the book quantity,
     * validate the user authentication token,
     * as only the user can add, update and delete from cart.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCartById(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer',
                'book_quantity' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 400);
            }

            $currentUser = JWTAuth::parseToken()->authenticate();
            if ($currentUser) {
                $user = User::checkUser($currentUser->id);
                if ($user) {
                    $cart = Cart::getCartByIdandUserId($request->id, $currentUser->id);
                    if ($cart) {
                        $bookData = Book::getBookById($cart->book_id);
                        if ($request->book_quantity > 0 && $request->book_quantity <= $bookData->quantity) {
                            $cart->book_quantity = $request->book_quantity;
                            if ($cart->save()) {
                                Log::info('Cart Updated Successfully');
                                Cache::forget('carts');
                                return response()->json([
                                    'message' => 'Cart Updated Successfully'
                                ], 201);
                            }
                            Log::error('Cart Not Updated');
                            throw new BookStoreException('Cart Not Updated', 102);
                        }
                        Log::error('Quantity Cannot be < 0 or > Quantity in Store');
                        throw new BookStoreException('Quantity Cannot be < 0 or > Quantity in Store', 406);
                    }
                    Log::error('Cart Not Found');
                    throw new BookStoreException('Cart Not Found', 404);
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
     *   path="/api/deletecart",
     *   summary="Delete the book from cart",
     *   description=" Delete cart ",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id"},
     *               @OA\Property(property="id", type="integer"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=200, description="Book Deleted Sucessfully from Cart"),
     *   @OA\Response(response=102, description="Cart Not Deleted"),
     *   @OA\Response(response=401, description="Invalid Authorization Token"),
     *   @OA\Response(response=404, description="Cart Not Found"),
     *   security = {
     *      { "Bearer" : {} }
     *   }
     * )
     * 
     * Function to delete the Cart by ID
     * using the cart id and user authentication token,
     * if the user have this cart id, delete successfully
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteCartById(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 400);
            }

            $currentUser = JWTAuth::parseToken()->authenticate();
            if ($currentUser) {
                $user = User::checkUser($currentUser->id);
                if ($user) {
                    $cart = Cart::getCartByIdandUserId($request->id, $currentUser->id);
                    if ($cart) {
                        if ($cart->delete()) {
                            Log::info('Book Deleted Sucessfully from Cart');
                            Cache::forget('carts');
                            return response()->json([
                                'message' => 'Book Deleted Sucessfully from Cart'
                            ], 200);
                        }
                        Log::error('Cart Not Deleted');
                        throw new BookStoreException('Cart Not Deleted', 102);
                    }
                    Log::error('Cart Not Found');
                    throw new BookStoreException('Cart Not Found', 404);
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
     * @OA\Get(
     *   path="/api/getallcartbooksofuser",
     *   summary="Get All Books Present in Cart",
     *   description=" Get All Books Present in Cart ",
     *   @OA\RequestBody(),
     *   @OA\Response(response=200, description="Books Present in Cart::"),
     *   @OA\Response(response=404, description="Book Not Found"),
     *   @OA\Response(response=401, description="Invalid Authorization Token"),
     *   security = {
     *      { "Bearer" : {} }
     *   }
     * )
     * 
     * Function to get all the books from the cart,
     * validate the user authentication token and 
     * get his cart books if he have any.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllCartBooksOfUser()
    {
        try {
            $currentUser = JWTAuth::parseToken()->authenticate();
            if ($currentUser) {
                $user = User::checkUser($currentUser->id);
                if ($user) {
                    $books = Cart::getAllCartBooksOfUser($currentUser->id);
                    if(empty($books))
                    {
                        Log::error('Book Not Found');
                        throw new BookStoreException('Book Not Found', 404);
                    }                    
                    Log::info('All Books Presnet in Cart are Fetched Successfully');
                    return response()->json([
                        'message' => 'Books Present in Cart::',
                        'Cart' => $books,
                    ], 200);
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
