<?php

namespace App\Http\Controllers;

use App\Exceptions\BookStoreException;
use App\Models\Book;
use App\Models\User;
use App\Models\Cart;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AddressController extends Controller
{
    /**
     * @OA\Post(
     *   path="/api/adduseraddress",
     *   summary="Add Address",
     *   description="User Can Add Address ",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"address", "landmark", "city", "state", "pincode"},
     *               @OA\Property(property="address", type="string"),
     *               @OA\Property(property="landmark", type="string"),
     *               @OA\Property(property="city", type="string"),
     *               @OA\Property(property="state", type="string"),
     *               @OA\Property(property="pincode", type="integer"),
     *               @OA\Property(property="address_type", type="string"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Address Added Successfully"),
     *   @OA\Response(response=401, description="Invalid Authorization Token"),
     *   @OA\Response(response=404, description="You are Not a User"),
     *   @OA\Response(response=406, description="Invalid Address Type"),
     *   security = {
     *      { "Bearer" : {} }
     *   }
     * )
     * 
     * Function to add user address,
     * taking address, landmark, city, state, pincode and address_type as credentials
     * validate the user authentication token and add the address
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function addUserAddress(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'address' => 'required|string|min:5|max:150',
                'landmark' => 'required|string|min:5|max:50',
                'city' => 'required|string|min:5|max:50',
                'state' => 'required|string|min:5|max:50',
                'pincode' => 'required|integer',
                'address_type' => ''
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors()->tojson(), 400);
            }

            $currentUser = JWTAuth::parseToken()->authenticate();
            if ($currentUser) {
                $user = User::checkUser($currentUser->id);
                if ($user) {
                    if (
                        strtolower($request->address_type) == 'home' || strtolower($request->address_type) == 'work'
                        || strtolower($request->address_type) == 'other' || $request->address_type == ''
                    ) {
                        $address = Address::addAddressToUser($request, $currentUser);
                        if ($address) {
                            Log::info('Address Added Successfully', ['UserID' => $currentUser->id]);
                            return response()->json([
                                'message' => 'Address Added Successfully'
                            ], 201);
                        }
                    }
                    Log::error('Invalid Address Type');
                    throw new BookStoreException('Invalid Address Type', 406);
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
     *   path="/api/updateuseraddress",
     *   summary="Update Address",
     *   description="User Can Update Address ",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id","address", "landmark", "city", "state", "pincode"},
     *               @OA\Property(property="id", type="integer"),
     *               @OA\Property(property="address", type="string"),
     *               @OA\Property(property="landmark", type="string"),
     *               @OA\Property(property="city", type="string"),
     *               @OA\Property(property="state", type="string"),
     *               @OA\Property(property="pincode", type="integer"),
     *               @OA\Property(property="address_type", type="string"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Address Updated Successfully"),
     *   @OA\Response(response=401, description="Invalid Authorization Token"),
     *   @OA\Response(response=404, description="Address Not Found, Add Address First"),
     *   @OA\Response(response=406, description="Invalid Address Type"),
     *   security = {
     *      { "Bearer" : {} }
     *   }
     * )
     * 
     * Function to update the address of the user,
     * take address_id and validate user authentication toke,
     * if found and user successfully validated,
     * update the address successfully
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAddress(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer',
                'address' => 'required|string|min:5|max:150',
                'landmark' => 'required|string|min:5|max:50',
                'city' => 'required|string|min:5|max:50',
                'state' => 'required|string|min:5|max:50',
                'pincode' => 'required|integer',
                'address_type' => ''
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 400);
            }

            $currentUser = JWTAuth::parseToken()->authenticate();
            if ($currentUser) {
                $user = User::checkUser($currentUser->id);
                if ($user) {
                    $address_exist = Address::getUserAddress($request->id, $currentUser->id);
                    if ($address_exist) {
                        if (
                            strtolower($request->address_type) == 'home' || strtolower($request->address_type) == 'work'
                            || strtolower($request->address_type) == 'other' || $request->address_type == ''
                        ) {
                            $address = Address::updateAddressOfUser($address_exist, $request);
                            if ($address) {
                                Log::info('Address Updated For User::', ['user_id', '=', $currentUser->id]);
                                return response()->json([
                                    'message' => 'Address Updated Successfully'
                                ], 201);
                            }
                        }
                        Log::error('Invalid Address Type');
                        throw new BookStoreException('Invalid Address Type', 406);
                    }
                    Log::error('Address Not Found');
                    throw new BookStoreException('Address Not Found, Add Address First', 404);
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
     *   path="/api/deleteuseraddress",
     *   summary="Delete Address",
     *   description=" Delete Address ",
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
     *   @OA\Response(response=201, description="Address Deleted Successfully"),
     *   @OA\Response(response=401, description="Invalid Authorization Token"),
     *   @OA\Response(response=404, description="Address Not Found"),
     *   security = {
     *      { "Bearer" : {} }
     *   }
     * )
     * 
     * Function to delete the address of a user,
     * take the address-id and validate user authentication token,
     * if validated successfully and found the address,
     * delete the address of the user successfully.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAddress(Request $request)
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
                    $address_exist = Address::getUserAddress($request->id, $currentUser->id);
                    if ($address_exist) {
                        if ($address_exist->delete()) {
                            Log::info('Address Deleted For User::', ['user_id', '=', $currentUser->id]);
                            return response()->json([
                                'message' => 'Address Deleted Successfully'
                            ], 200);
                        }
                    }
                    Log::error('Address Not Found');
                    throw new BookStoreException('Address Not Found', 404);
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
     *   path="/api/getuseraddresses",
     *   summary="Get addresses ",
     *   description=" Get User Addresses ",
     *   @OA\RequestBody(),
     *   @OA\Response(response=200, description="Addresses Fetched Successfully"),
     *   @OA\Response(response=404, description="Addresses Not Found"),
     *   @OA\Response(response=401, description="Invalid Authorization Token"),
     *   security = {
     *      { "Bearer" : {} }
     *   }
     * )
     * 
     * Function to get all the address of the user,
     * validate the user authentication token and
     * fetch the addresses if found for that user.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAddress()
    {
        try {
            $currentUser = JWTAuth::parseToken()->authenticate();
            if ($currentUser) {
                $user = User::checkUser($currentUser->id);
                if ($user) {
                    $addresses = Address::getUserAddresses($currentUser->id);
                    if ($addresses) {
                        Log::info('Addresses Fetched For User::', ['user_id', '=', $currentUser->id]);
                        return response()->json([
                            'message' => 'Addresses Fetched Successfully',
                            'addresses' => $addresses
                        ], 200);
                    }
                    Log::error('Addresses Not Found');
                    throw new BookStoreException('Addresses Not Found', 404);
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
