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
        //'image',
        'Price',
        'quantity',
        'user_id'
    ];

    public static function addNewBook($request, $currentUser)
    {
        $book = new Book();
        $book->name = $request->input('name');
        $book->description = $request->input('description');
        $book->author = $request->input('author');
        $book->price = $request->input('price');
        $book->quantity = $request->input('quantity');
        $book->user_id = $currentUser->id;
        $book->save();

        return $book;
    }

    public static function updateBook($request, $book)
    {
        $book->name = $request->input('name');
        $book->description = $request->input('description');
        $book->author = $request->input('author');
        $book->price = $request->input('price');
        $book->quantity = $request->input('quantity');
        $book->save();

        return $book;
    }

    public static function addQuantity($bookDetails, $quantity)
    {
        $bookDetails->quantity += $quantity;
        $bookDetails->save();

        return $bookDetails;
    }

    public static function getBookByName($name)
    {
        $bookDetails = Book::where('name', $name)->first();
        return $bookDetails;
    }

    public static function getBookByIdandUserId($bookId, $userId)
    {
        $book = Book::where('id', $bookId)->where('user_id', $userId)->first();
        return $book;
    }

    public static function getBookById($bookId)
    {
        $book = Book::where('id', $bookId)->first();
        return $book;
    }

    public static function searchBook($searchKey)
    {
        $userbooks = Book::select('books.id', 'books.name', 'books.description', 'books.author', 'books.price', 'books.quantity')
        ->Where('books.name', 'like', '%' . $searchKey . '%')
        ->orWhere('books.author', 'like', '%' . $searchKey . '%')
        ->orWhere('books.description', 'like', '%' . $searchKey . '%')
        ->get();

        return $userbooks;
    }

    public static function ascendingOrder()
    {
        return Book::orderBy('price')->paginate(4);
    }

    public static function descendingOrder()
    {
        return Book::orderBy('price', 'desc')->paginate(4);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // public static function addNewBook($request, $currentUser)
    // {
    //     $book = new Book();
    //     $path = Storage::disk('s3')->put('bookimagebook', $request->image);
    //     $url = env('AWS_URL') . $path;
    //     $book->name = $request->input('name');
    //     $book->description = $request->input('description');
    //     $book->author = $request->input('author');
    //     $book->image = $url;
    //     $book->Price = $request->input('Price');
    //     $book->quantity = $request->input('quantity');
    //     $book->user_id = $currentUser->id;
    //     $book->save();

    //     return $book;
    // }

    // public static function updateBook($request)
    // {
    //     $book = new Book();

    //     $book->name = $request->input('name');
    //     $book->description = $request->input('description');
    //     $book->author = $request->input('author');
    //     $book->Price = $request->input('Price');
    //     $book->quantity = $request->input('quantity');

    //     if ($request->image) {
    //         $path = str_replace(env('AWS_URL'), '', $book->image);

    //         if (Storage::disk('s3')->exists($path)) {
    //             Storage::disk('s3')->delete($path);
    //         }
    //         $path = Storage::disk('s3')->put('bookimagebook', $request->image);
    //         $url = env('AWS_URL') . $path;
    //         $book->image = $url;
    //     }

    //     $book->save();

    //     return $book;
    // }

    // public static function deleteBookImage($bookDetails)
    // {
    //     $path = str_replace(env('AWS_URL'), '', $bookDetails->image);
    //     if (Storage::disk('s3')->exists($path)) {
    //         Storage::disk('s3')->delete($path);
    //     }
    // }
}
