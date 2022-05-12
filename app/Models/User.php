<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'role',
        'first_name',
        'last_name',
        'phone_number',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Creates a new user with the attributes given
     * 
     * @return array
     */
    public static function createUser($request)
    {
        $user = new User();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->phone_number = $request->phone_number;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        if($request->role)
        {
            $user->role = $request->role;
        }
        $user->save();
        
        return $user;
    }

    /**
     * Check User, whether user or admin,
     * using userID to check
     * 
     * @return array
     */
    public static function checkAdminUser($currentUserId)
    {
        $user = User::where('role', 'admin')->where('id', $currentUserId)->first();
        return $user;
    }

    /**
     * Check User, whether user or admin
     * using userID to check
     * 
     * @return array
     */
    public static function checkUser($currentUserId)
    {
        $user = User::where('role', 'user')->where('id', $currentUserId)->first();
        return $user;
    }

    /**
     * Function to get user details by email
     * Passing the email as parameter
     * 
     * @return array
     */
    public static function getUserByEmail($email)
    {
        $user = User::where('email', $email)->first();
        return $user;
    }

    /**
     * Function to Update the password with new password
     * Passing the User and the new_password as parameters
     * 
     * @return array
     */
    public static function updatePassword($user, $new_password)
    {
        $user->password = bcrypt($new_password);
        $user->save();
        return $user;
    }

    /**
     * Mutator for first name attribute
     * Before saving it to database first letter will be changed to upper case
     */
    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = ucfirst($value);
    }

    /**
     * Mutator for last name attribute
     * Before saving it to database first letter will be changed to upper case
     */
    public function setLastNameAttribute($value)
    {
        $this->attributes['last_name'] = ucfirst($value);
    }

    /**
     * Accessor for first name attribute
     * When user is retrived from database, 
     * first letter of first name will be upper case and 
     * Mr/s. will be added while displaying
     */
    public function getFirstNameAttribute($value)
    {
        return 'Mr/s. ' . ucfirst($value);
    }

    public function books()
    {
        return $this->hasMany('App\Models\Book');
    }
    public function carts()
    {
        return $this->hasMany('App\Models\Cart');
    }
    public function addresses()
    {
        return $this->hasMany('App\Models\Address');
    }
    public function orderss()
    {
        return $this->hasMany('App\Models\Order');
    }
}
