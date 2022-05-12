<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Book extends Model
{
    use HasFactory;

    protected $table = "books";
    protected $fillable = [
        'name',
        'description',
        'author',
        'image',
        'price',
        'quantity',
        'user_id'
    ];

    // public static function addNewBook($request, $currentUser)
    // {
    //     $book = new Book();
    //     $book->name = $request->input('name');
    //     $book->description = $request->input('description');
    //     $book->author = $request->input('author');
    //     $book->price = $request->input('price');
    //     $book->quantity = $request->input('quantity');
    //     $book->user_id = $currentUser->id;
    //     $book->save();

    //     return $book;
    // }

    /**
     * Function to add new book to the store,
     * passing all the required credentials as parameters,
     * 
     * @return array
     */
    public static function addNewBook($request, $currentUser)
    {
        $book = new Book();
        $path = Storage::disk('s3')->put('book_image', $request->image);
        $url = env('AWS_URL') . $path;
        $book->name = $request->input('name');
        $book->description = $request->input('description');
        $book->author = $request->input('author');
        $book->image = $url;
        $book->price = $request->input('price');
        $book->quantity = $request->input('quantity');
        $book->user_id = $currentUser->id;
        $book->save();

        return $book;
    }

    // public static function updateBook($request, $book)
    // {
    //     $book->name = $request->input('name');
    //     $book->description = $request->input('description');
    //     $book->author = $request->input('author');
    //     $book->price = $request->input('price');
    //     $book->quantity = $request->input('quantity');
    //     $book->save();

    //     return $book;
    // }

    /**
     * Function to update the book in the store,
     * passing all the required credentials as parameters,
     * 
     * @return array
     */
    public static function updateBook($request, $book)
    {
        $book->name = $request->input('name');
        $book->description = $request->input('description');
        $book->author = $request->input('author');
        $book->price = $request->input('price');
        $book->quantity = $request->input('quantity');
        if ($request->image) {
            $path = str_replace(env('AWS_URL'), '', $book->image);

            if (Storage::disk('s3')->exists($path)) {
                Storage::disk('s3')->delete($path);
            }
            $path = Storage::disk('s3')->put('book_image', $request->image);
            $url = env('AWS_URL') . $path;
            $book->image = $url;
        }

        $book->save();

        return $book;
    }

    /**
     * Function to delete the book image from aws s3 bucket,
     * passing the book details as parameter,
     * 
     * @return array
     */
    public static function deleteBookImage($bookDetails)
    {
        $path = str_replace(env('AWS_URL'), '', $bookDetails->image);
        if (Storage::disk('s3')->exists($path)) {
            Storage::disk('s3')->delete($path);
        }
    }

    /**
     * Function to add quantity to the existing book,
     * passing the book details and quantity as parameters
     * 
     * @return array
     */
    public static function addQuantity($bookDetails, $quantity)
    {
        $bookDetails->quantity += $quantity;
        $bookDetails->save();

        return $bookDetails;
    }

    /**
     * Function to get book by name,
     * passing the book name as parameter
     * 
     * @return array
     */
    public static function getBookByName($name)
    {
        $bookDetails = Book::where('name', $name)->first();
        return $bookDetails;
    }

    /**
     * Function to get book by bookID and userID,
     * passing the bookID and userID as parameters
     * 
     * @return array
     */
    public static function getBookByIdandUserId($bookId, $userId)
    {
        $book = Book::where('id', $bookId)->where('user_id', $userId)->first();
        return $book;
    }

    /**
     * Function to get book by bookID,
     * passing the bookID as parameter
     * 
     * @return array
     */
    public static function getBookById($bookId)
    {
        $book = Book::where('id', $bookId)->first();
        return $book;
    }

    /**
     * Function to search a book by a key,
     * to be found in book name or author or description,
     * passing the key as parameter
     * 
     * @return array
     */
    public static function searchBook($searchKey)
    {
        $userbooks = Book::select('books.id', 'books.name', 'books.description', 'books.author', 'books.price', 'books.quantity')
        ->Where('books.name', 'like', '%' . $searchKey . '%')
        ->orWhere('books.author', 'like', '%' . $searchKey . '%')
        ->orWhere('books.description', 'like', '%' . $searchKey . '%')
        ->get();

        return $userbooks;
    }

    /**
     * Function to sort the books in store in ascending order,
     * based on the price of the book
     * 
     * @return array
     */
    public static function ascendingOrder()
    {
        return Book::orderBy('price')->paginate(4);
    }

    /**
     * Function to sort the books in store in descending order
     * based on the price of the book
     * 
     * @return array
     */
    public static function descendingOrder()
    {
        return Book::orderBy('price', 'desc')->paginate(4);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
