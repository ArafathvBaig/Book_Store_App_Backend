<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = "orders";

    protected $fillable = [
        'user_id',
        'cart_id',
        'address_id',
        'total_price',
        'order_id'
    ];

    public static function placeOrder($request, $currentUser, $book, $cart)
    {
        $total_price = $book->price * $cart->book_quantity;
        $order = new Order();
        $order->user_id = $currentUser->id;
        $order->cart_id = $request->cart_id;
        $order->address_id = $request->address_id;
        $order->total_price = $total_price;
        $order->order_id = $order->unique_code(9);
        $order->save();

        return $order;        
    }

    public static function getOrder($cartId)
    {
        $order = Order::where('cart_id', $cartId)->first();

        return $order;
    }

    public static function getOrderByOrderID($orderID, $userID)
    {
        $order = Order::where('order_id', $orderID)->where('user_id', $userID)->first();

        return $order;
    }

    /**
     * base_convert – Convert a number between arbitrary bases
     * sha1 – Calculate the sha1 hash of a string.
     * uniqid – Generate a unique ID.
     * mt_rand – Generate a random value via the Mersenne Twister Random Number Generator.
     */
    public function unique_code($limit = 9)
    {
        return substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, $limit);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
