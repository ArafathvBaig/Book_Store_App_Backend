<?php

namespace App\Http\Controllers;

use App\Exceptions\BookStoreException;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    /**
     * @OA\Post(
     *  path="/api/register",
     *  summary="register",
     *  description="Register a new user",
     *  @OA\RequestBody(
     *      @OA\JsonContent(),
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *              type="object",
     *              required={"first_name","last_name","email","password","password_confirmation","phone_number"},
     *              @OA\Property(property="first_name", type="string"),
     *              @OA\Property(property="last_name", type="string"),
     *              @OA\Property(property="phone_number", type="string"),
     *              @OA\Property(property="email", type="email"),
     *              @OA\Property(property="password", type="string"),
     *              @OA\Property(property="password_confirmation", type="string"),
     *              @OA\Property(property="role"),
     *          ),  
     *      ),
     *  ),
     *  @OA\Response(response=201, description="User Successfully Registered"),
     *  @OA\Response(response=401, description="The email has already been taken"),
     *  @OA\Response(response=406, description="Invalid Role Input")
     * )
     * 
     * This Function take first_name, last_name, email and password
     * to register a new user into the database
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|min:3',
                'last_name' => 'required|string|min:3',
                'phone_number' => 'required|string|min:10|max:12',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:6|max:50',
                'password_confirmation' => 'required|same:password',
                'role' => ''
            ]);

            $user = User::getUserByEmail($request->email);
            if ($user) {
                Log::info('The email has already been taken: ' . $user->email);
                throw new BookStoreException('The email has already been taken', 401);
            }

            if ($validator->fails()) {
                return response()->json([$validator->errors()], 400);
            }

            if($request->role == '' || strtolower($request->role) == "admin" || strtolower($request->role) == "user")
            {
                User::createUser($request);
                Log::info('User Successfully Registered.');
                return response()->json([
                    'message' => 'User Successfully Registered'
                ], 201);
            }
            Log::error('Invalid Role Input');
            throw new BookStoreException('Invalid Role Input', 406);
        } catch (BookStoreException $exception) {
            return response()->json([
                'message' => $exception->message()
            ], $exception->statusCode());
        }
    }

    /**
     * @OA\Post(
     *  path="/api/login",
     *  summary="login",
     *  description="Login by email, password",
     *  @OA\RequestBody(
     *      @OA\JsonContent(),
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *              type="object",
     *              required={"email","password"},
     *              @OA\Property(property="email", type="email"),
     *              @OA\Property(property="password", type="string")
     *          ),  
     *      ),
     *  ),
     *  @OA\Response(response=404, description="Not a Registered Email"),
     *  @OA\Response(response=402, description="Wrong Password"),
     *  @OA\Response(response=200, description="Login Successful")
     * )
     * 
     * This Function takes user registered email and password and
     * checks the password is correct or not and login the user and
     * give a login token in the response
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            $credentials = $request->only('email', 'password');

            //valid credential
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string|min:6|max:50'
            ]);

            //Send failed response if request is not valid
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            //Request is validated
            $user = User::getUserByEmail($request->input('email'));
            if (!$user) {
                Log::error('Not a Registered Email');
                throw new BookStoreException('Not a Registered Email', 404);
            } elseif (!Hash::check($request->password, $user->password)) {
                Log::error('Wrong Password');
                throw new BookStoreException('Wrong Password', 402);
            } else {
                //Token created, return with success response and jwt token
                $token = JWTAuth::attempt($credentials);
                Log::info('Login Successful');
                return response()->json([
                    'success' => 'Login Successful',
                    'token' => $token
                ], 200);
            }
        } catch (BookStoreException $exception) {
            return response()->json([
                'message' => $exception->message()
            ], $exception->statusCode());
        }
    }

    /**
     * * @OA\Post(
     *   path="/api/logout",
     *   summary="logout",
     *   description="logout user",
     *   @OA\RequestBody(),
     *   @OA\Response(response=200, description="User Successfully Logged Out"),
     *   @OA\Response(response=401, description="Invalid Authorization Token"),
     *   security={
     *       {"Bearer": {}}
     *     }
     * )
     * This Function takes user authorization token and 
     * logout the user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $user = auth()->logout();

        if (!$user) {
            return response()->json([
                'message' => 'User Successfully Logged Out'
            ], 200);
        }
        return response()->json([
            'message' => 'Invalid Authorization Token'
        ], 401);
    }

    public function get_user(Request $request)
    {
        $this->validate($request, [
            'token' => 'required'
        ]);

        $user = JWTAuth::authenticate($request->token);

        return response()->json(['user' => $user]);
    }
}
