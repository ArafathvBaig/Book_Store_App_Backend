<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;
    protected $table = 'ratings';
    protected $fillable = [
        'user_id',
        'book_id',
        'order_id',
        'user_rating'
    ];

    public static function addRating($request, $userId, $bookId)
    {
        $rating = new Rating();
        $rating->user_id = $userId;
        $rating->book_id = $bookId;
        $rating->order_id = $request->order_id;
        $rating->user_rating = $request->user_rating;
        $rating->save();

        return $rating;
    }

    public static function getRating($orderId, $userId, $bookId)
    {
        $rating = Rating::where('book_id', $bookId)
            ->where('user_id', $userId)
            ->where('order_id', $orderId)->first();

        return $rating;
    }

    public static function getAverageRating($book_id)
    {
        // $books = Book::leftJoin('ratings', 'ratings.book_id', '=', 'books.id')
        //     ->select('books.id','books.name','books.description','books.author','books.price','books.quantity',
        //     'books.user_id',Rating::where('book_id', 'books.id')->pluck('user_rating')->avg())->paginate(4);

        $avgRating = Rating::where('book_id', $book_id)
            ->pluck('user_rating')->avg();

        return $avgRating;
    }

    public function users()
    {
        return $this->belongsTo(User::class);
    }
}
