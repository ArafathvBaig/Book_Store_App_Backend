<?php

namespace App\Http\Controllers;

use App\Exceptions\BookStoreException;
use App\Models\Book;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class BookController extends Controller
{
    /**
     * @OA\Post(
     *  path="/api/addbook",
     *  summary="Add a new Book",
     *  description="Only Admin Can Add Book ",
     *  @OA\RequestBody(
     *      @OA\JsonContent(),
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *              type="object",
     *              required={"name","description","author","image","Price","quantity"},
     *              @OA\Property(property="name", type="string"),
     *              @OA\Property(property="description", type="string"),
     *              @OA\Property(property="author", type="string"),
     *              @OA\Property(property="image", type="file"),
     *              @OA\Property(property="price", type="decimal"),
     *              @OA\Property(property="quantity", type="integer"),
     *          ),
     *      ),
     *  ),
     *  @OA\Response(response=201, description="Book Added Successfully"),
     *  @OA\Response(response=401, description="Invalid Authorization Token"),
     *  @OA\Response(response=404, description="User Is Not a Admin"),
     *  @OA\Response(response=406, description="Invalid Price Input"),
     *  @OA\Response(response=409, description="Book Already Exits in BookStore"),
     *  security = {
     *      { "Bearer" : {} }
     *  }
     * )
     * 
     * Function to add a new book,
     * taking all credentials i.e name, description, author, price, quantity
     * and validating the admin user and add the book
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function addBook(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|min:3|max:50',
                'description' => 'required|string|min:5|max:1000',
                'author' => 'required|string|min:5|max:50',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,tiff|max:2048',
                'price' => 'required',
                'quantity' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors()->tojson(), 400);
            }

            $currentUser = JWTAuth::parseToken()->authenticate();
            if ($currentUser) {
                $user = User::checkAdminUser($currentUser->id);
                if (!$user) {
                    Log::error('User Is Not a Admin');
                    throw new BookStoreException('User Is Not a Admin', 404);
                }
                $bookDetails = Book::getBookByName($request->name);
                if ($bookDetails) {
                    Log::error('Book Already Exits in BookStore');
                    throw new BookStoreException('Book Already Exits in BookStore', 409);
                }
                if ($request->price <= 0) {
                    throw new BookStoreException('Invalid Price Input', 406);
                }
                if ($request->quantity <= 0) {
                    throw new BookStoreException('Invalid Quantity Input', 406);
                }
                $book = Book::addNewBook($request, $currentUser);
                Cache::remember('books', 3600, function () {
                    return DB::table('books')->get();
                });
                Log::info('Book Added Successfully', ['AdminID' => $currentUser->id]);
                return response()->json([
                    'message' => 'Book Added Successfully'
                ], 201);
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
     *  path="/api/updatebook",
     *  summary="Update Book",
     *  description="Only Admin Can Update Book ",
     *  @OA\RequestBody(
     *      @OA\JsonContent(),
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *              type="object",
     *              required={"id","name","description","author","image","Price","quantity"},
     *              @OA\Property(property="id", type="integer"),
     *              @OA\Property(property="name", type="string"),
     *              @OA\Property(property="description", type="string"),
     *              @OA\Property(property="author", type="string"),
     *              @OA\Property(property="image", type="file"),
     *              @OA\Property(property="price", type="decimal"),
     *              @OA\Property(property="quantity", type="integer"),
     *          ),
     *      ),
     *  ),
     *  @OA\Response(response=201, description="Book Updated Successfully"),
     *  @OA\Response(response=401, description="Invalid Authorization Token"),
     *  @OA\Response(response=404, description="Book Not Found"),
     *  @OA\Response(response=406, description="Invalid Price Input"),
     *  @OA\Response(response=409, description="Book Already Exits in BookStore"),
     *  security = {
     *      { "Bearer" : {} }
     *  }
     * )
     * 
     * Function to update an existing book by its id,
     * taking all the credentials to update i.e id, name, description,
     * author, price, quantity and validating the admin user authentication token
     * and update the book if belongs to that user
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateBookByBookId(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer',
                'name' => 'required|string|min:3|max:50',
                'description' => 'required|string|min:5|max:1000',
                'author' => 'required|string|min:5|max:50',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,tiff|max:2048',
                'price' => 'required',
                'quantity' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 400);
            }

            $currentUser = JWTAuth::parseToken()->authenticate();
            if (!$currentUser) {
                Log::error('Invalid Authorization Token');
                throw new BookStoreException('Invalid Authorization Token', 401);
            }
            $user = User::checkAdminUser($currentUser->id);
            if (!$user) {
                Log::error('User Is Not a Admin');
                throw new BookStoreException('User Is Not a Admin', 404);
            }

            $bookData = Book::getBookByIdandUserId($request->id, $currentUser->id);
            if (!$bookData) {
                Log::error('Book Not Found');
                throw new BookStoreException('Book Not Found', 404);
            }

            $bookDetails = Book::getBookByName($request->name);
            if ($bookDetails && $bookDetails->id != $request->id) {
                Log::error('Book Already Exits in BookStore');
                throw new BookStoreException('Book Already Exits in BookStore', 409);
            }
            if ($request->price <= 0) {
                throw new BookStoreException('Invalid Price Input', 406);
            }
            if ($request->quantity <= 0) {
                throw new BookStoreException('Invalid Quantity Input', 406);
            }
            $book = Book::updateBook($request, $bookData);
            Cache::forget('books');
            if ($book) {
                Log::info('Book Updated Successfully', ['AdminID' => $currentUser->id]);
                return response()->json([
                    'message' => 'Book Updated Successfully'
                ], 201);
            }
        } catch (BookStoreException $exception) {
            return response()->json([
                'message' => $exception->message()
            ], $exception->statusCode());
        }
    }

    /**
     * @OA\Post(
     *  path="/api/addquantity",
     *  summary="Add Quantity to Existing Book",
     *  description=" Add Book Quantity ",
     *  @OA\RequestBody(
     *      @OA\JsonContent(),
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *              type="object",
     *              required={"id", "quantity"},
     *              @OA\Property(property="id", type="integer"),
     *              @OA\Property(property="quantity", type="integer"),
     *          ),
     *      ),
     *  ),
     *  @OA\Response(response=201, description="Book Quantity Added Successfully"),
     *  @OA\Response(response=401, description="Invalid Authorization Token"),
     *  @OA\Response(response=406, description="Invalid Quantity Input"),
     *  @OA\Response(response=404, description="Book Not Found"),
     *  security = {
     *      { "Bearer" : {} }
     *  }
     * )
     * 
     * Function to add quantity to an existing book,
     * taking id and quantity as credentials,
     * authenticating the token and validate user is Admin or not
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addQuantityToExistingBook(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer',
                'quantity' => 'required|integer'
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 400);
            }

            $currentUser = JWTAuth::parseToken()->authenticate();
            if (!$currentUser) {
                Log::error('Invalid Authorization Token');
                throw new BookStoreException('Invalid Authorization Token', 401);
            }
            $user = User::checkAdminUser($currentUser->id);
            if (!$user) {
                Log::error('User Is Not a Admin');
                throw new BookStoreException('User Is Not a Admin', 404);
            }

            $bookDetails = Book::getBookByIdandUserId($request->id, $currentUser->id);
            if (!$bookDetails) {
                Log::error('Book Not Found');
                throw new BookStoreException('Book Not Found', 404);
            }
            if ($request->quantity <= 0) {
                throw new BookStoreException('Invalid Quantity Input', 406);
            }
            $book = Book::addQuantity($bookDetails, $request->quantity);
            Cache::forget('books');
            Log::info('Book Quantity Added Successfully');
            if ($book) {
                return response()->json([
                    'message' => 'Book Quantity Added Successfully'
                ], 201);
            }
        } catch (BookStoreException $exception) {
            return response()->json([
                'message' => $exception->message()
            ], $exception->statusCode());
        }
    }

    /**
     * @OA\Post(
     *  path="/api/deletebook",
     *  summary="Delete the book from BookStoreApp",
     *  description=" Delete Book ",
     *  @OA\RequestBody(
     *      @OA\JsonContent(),
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *              type="object",
     *              required={"id"},
     *              @OA\Property(property="id", type="integer"),
     *          ),
     *      ),
     *  ),
     *  @OA\Response(response=200, description="Book Deleted Sucessfully"),
     *  @OA\Response(response=401, description="Invalid Authorization Token"),
     *  @OA\Response(response=404, description="Book Not Found"),
     *  security = {
     *      {"Bearer" : {}}
     *  }
     * )
     * 
     * Function to delete book by its id,
     * passing the id and validating the admin user and 
     * delete the book if the book belongs to that user
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteBookByBookId(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 400);
            }

            $currentUser = JWTAuth::parseToken()->authenticate();
            if (!$currentUser) {
                Log::error('Invalid Authorization Token');
                throw new BookStoreException('Invalid Authorization Token', 401);
            }

            $user = User::checkAdminUser($currentUser->id);
            if (!$user) {
                Log::error('User Is Not a Admin');
                throw new BookStoreException('User Is Not a Admin', 404);
            }

            $bookDetails = Book::getBookByIdandUserId($request->id, $currentUser->id);
            if (!$bookDetails) {
                Log::error('Book Not Found');
                throw new BookStoreException('Book Not Found', 404);
            }
            Book::deleteBookImage($bookDetails);
            if ($bookDetails->delete()) {
                Log::info('Book Deleted Successfully', ['AdminID' => $currentUser->id, 'BookID' => $request->id]);
                Cache::forget('books');
                return response()->json([
                    'message' => 'Book Deleted Sucessfully'
                ], 200);
            }
        } catch (BookStoreException $exception) {
            return response()->json([
                'message' => $exception->message()
            ], $exception->statusCode());
        }
    }

    /**
     * @OA\Get(
     *  path="/api/getallbooks",
     *  summary="Display All Books",
     *  description=" Display All Books Present in the BookStore ",
     *  @OA\RequestBody(),
     *  @OA\Response(response=200, description="Books Available in the Bookstore are::"),
     *  @OA\Response(response=404, description="Book Not Found"),
     * )
     * 
     * Function to get all the books from the Book Store
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllBooks()
    {
        try {
            $books = Book::paginate(4);

            if (!$books) {
                Log::error('Book Not Found');
                throw new BookStoreException('Book Not Found', 404);
            }
            Cache::remember('books', 3600, function () {
                return DB::table('books')->get();
            });
            Log::info('Books Fetched Successfully');
            return response()->json([
                'message' => 'Books Available in the Bookstore are::',
                'books' => $books
            ], 200);
        } catch (BookStoreException $exception) {
            return response()->json([
                'message' => $exception->message()
            ], $exception->statusCode());
        }
    }

    /** 
     * @OA\Post(
     *  path="/api/searchbookbykey",
     *  summary="search the book from BookStoreApp",
     *  description=" Search Book ",
     *  @OA\RequestBody(
     *      @OA\JsonContent(),
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *              type="object",
     *              required={"search_book"},
     *              @OA\Property(property="search_book", type="string"),
     *          ),
     *      ),
     *  ),
     *  @OA\Response(response=200, description="Book Searched Successfully"),
     *  @OA\Response(response=404, description="Book Not Found"),
     *  @OA\Response(response=401, description="Invalid Authorization Token"),
     *  security = {
     *      { "Bearer" : {} }
     *  }
     * )
     * 
     * Function to search book by a key
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchBookByKey(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'search_book' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 400);
            }

            $searchKey = $request->input('search_book');
            $currentUser = JWTAuth::parseToken()->authenticate();
            if ($currentUser) {
                $user = User::checkUser($currentUser->id);
                if ($user) {
                    $userbooks = Book::searchBook($searchKey);
                    if ($userbooks) {
                        Log::info('Book Searched Successfully');
                        return response()->json([
                            'message' => 'Book Searched Successfully',
                            'books' => $userbooks
                        ], 200);
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
     * @OA\Get(
     *  path="/api/sortbookbypricelowtohigh",
     *  summary="Sort Book by Price Low to High",
     *  description="Sort Book Ascending Order",
     *  @OA\RequestBody(),
     *  @OA\Response(response=200, description="Book Sorted Successfully in Ascending Order"),
     *  @OA\Response(response=401, description="Invalid Authorization Token"),
     *  @OA\Response(response=404, description="Book Not Found"),
     *  security = {
     *      { "Bearer" : {} }
     *  }
     * )
     * 
     * Function to sort book by price,
     * Low to High
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function sortBookByPriceLowToHigh()
    {
        try {
            $currentUser = JWTAuth::parseToken()->authenticate();
            if ($currentUser) {
                $user = User::checkUser($currentUser->id);
                if ($user) {
                    //$bookDetails = Book::orderby('price')->paginate(4);
                    $bookDetails = Book::ascendingOrder();
                    if ($bookDetails) {
                        Log::info('Books Sorted Successfully in Ascending Order');
                        return response()->json([
                            'message' => 'Book Sorted Successfully in Ascending Order',
                            'books' => $bookDetails
                        ], 200);
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
     * @OA\Get(
     *  path="/api/sortbookbypricehightolow",
     *  summary="Sort Book by Price High to Low",
     *  description="Sort Book Descending Order",
     *  @OA\RequestBody(),
     *  @OA\Response(response=200, description="Book Sorted Successfully in Descending Order"),
     *  @OA\Response(response=401, description="Invalid Authorization Token"),
     *  @OA\Response(response=404, description="Book Not Found"),
     *  security = {
     *      { "Bearer" : {} }
     *  }
     * )
     * 
     * Function to sort book by price,
     * High to Low
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function sortBookByPriceHighToLow()
    {
        try {
            $currentUser = JWTAuth::parseToken()->authenticate();
            if ($currentUser) {
                $user = User::checkUser($currentUser->id);
                if ($user) {
                    //$bookDetails = Book::orderby('price', 'desc')->paginate(4);
                    $bookDetails = Book::descendingOrder();
                    if ($bookDetails) {
                        Log::info('Books Sorted Successfully in Descending Order');
                        return response()->json([
                            'message' => 'Book Sorted Successfully in Descending Order',
                            'books' => $bookDetails
                        ], 200);
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
