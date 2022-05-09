<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $table = "addresses";
    protected $fillable = [
        'address',
        'landmark',
        'city',
        'state',
        'pincode',
        'address_type',
        'user_id'
    ];

    public static function addAddressToUser($request, $currentUser)
    {
        $address = new Address();
        $address->address = $request->address;
        $address->landmark = $request->landmark;
        $address->city = $request->city;
        $address->state = $request->state;
        $address->pincode = $request->pincode;
        if ($request->address_type) {
            $address->address_type = strtolower($request->address_type);
        }
        $address->user_id = $currentUser->id;
        $address->save();

        return $address;
    }

    public static function updateAddressOfUser($address, $request)
    {
        $address->address = $request->address;
        $address->landmark = $request->landmark;
        $address->city = $request->city;
        $address->state = $request->state;
        $address->pincode = $request->pincode;
        if ($request->address_type) {
            $address->address_type = strtolower($request->address_type);
        }
        $address->save();

        return $address;
    }

    public static function getUserAddress($addressId, $userId)
    {
        $address = Address::where('id', $addressId)->where('user_id', $userId)->first();

        return $address;
    }

    public static function getUserAddresses($userId)
    {
        $address = Address::where('user_id', $userId)->get();

        return $address;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
