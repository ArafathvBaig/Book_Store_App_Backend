<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
    protected $table="carts";
    protected $fillable = [
        'book_id',
        'book_quantity',
        'user_id'
    ];

    public static function addBookToYourCart($request, $user){
        $cart = new Cart();
        $cart->user_id = $user->id;
        $cart->book_id = $request->book_id;
        if($request->book_quantity){
            $cart->book_quantity = $request->book_quantity;
        }
        $cart->save();

        return $cart;
    }

    public static function getCartedBook($bookId, $userId)
    {
        $book = Cart::where('book_id', $bookId)->where('user_id', $userId)->first();

        return $book;
    }

    public static function getCartByIdandUserId($cartId, $userId){
        $cart = Cart::where('id', $cartId)->where('user_id', $userId)->first();

        return $cart;
    }

    public static function getAllCartBooksOfUser($userId)
    {
        $books = Cart::leftJoin('books', 'carts.book_id', '=', 'books.id')
        ->select('books.id', 'books.name', 'books.author', 'books.description', 'books.price', 'carts.book_quantity')
        ->where('carts.user_id', '=', $userId)->paginate(4);

        return $books;
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
