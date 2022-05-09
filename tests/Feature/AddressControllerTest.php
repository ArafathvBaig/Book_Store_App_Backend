<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AddressControllerTest extends TestCase
{
    protected static $token;
    protected static $id;
    public static function setUpBeforeClass(): void
    {
        self::$id = "9";
        self::$token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODAwMFwvYXBpXC9sb2dpbiIsImlhdCI6MTY1MjA4MzQxMywiZXhwIjoxNjUyMDg3MDEzLCJuYmYiOjE2NTIwODM0MTMsImp0aSI6IkhOMFdxUjJORzFXWjBSVk4iLCJzdWIiOjgsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.i01WLUtzuzMKB047O3190zLRqe8JjD_LEzF-whI51ds";
    }

    /**
     * Successfull Add address for a user
     * This test is to add address for a user
     * by taking address, landmark, city, state, pincode and address_type as credentials
     * 
     * @test
     */
    public function successfulAddBookToCartTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/adduseraddress', [
                "address" => "Kopurivari Palem",
                "landmark" => "Near Mosque",
                "city" => "Repalle",
                "state" => "Andhra Pradesh",
                "pincode" => "522262",
                "address_type" => "home",
                "token" => self::$token
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'Address Added Successfully']);
    }

    /**
     * UnSuccessfull Add address for a user
     * This test is to add address for a user
     * by taking address, landmark, city, state, pincode and address_type as credentials
     * Using Wrong Credentials for unsuccessful test
     * 
     * @test
     */
    public function unSuccessfulAddBookToCartTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/adduseraddress', [
                "address" => "Kopurivari Palem",
                "landmark" => "Near Mosque",
                "city" => "Repalle",
                "state" => "Andhra Pradesh",
                "pincode" => "522262",
                "address_type" => "office",
                "token" => self::$token
            ]);
        $response->assertStatus(406)->assertJson(['message' => 'Invalid Address Type']);
    }

    /**
     * Successfull Update address for a user
     * This test is to Update address for a user
     * by taking id, address, landmark, city, state, pincode and address_type as credentials
     * 
     * @test
     */
    public function successfulUpdateBookInCartTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/updateuseraddress', [
                "id" => self::$id,
                "address" => "Kopuri Vari Palem, Nizampatnam to Repalle Main Road",
                "landmark" => "Near Mosque",
                "city" => "Repalle",
                "state" => "Andhra Pradesh",
                "pincode" => "522262",
                "address_type" => "work",
                "token" => self::$token
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'Address Updated Successfully']);
    }

    /**
     * UnSuccessfull Update address for a user
     * This test is to Update address for a user
     * by taking id, address, landmark, city, state, pincode and address_type as credentials
     * Using Wrong Credentials for unsuccessful test
     * 
     * @test
     */
    public function unSuccessfulUpdateBookInCartTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/updateuseraddress', [
                "id" => self::$id,
                "address" => "Kopurivari Palem",
                "landmark" => "Near Mosque",
                "city" => "Repalle",
                "state" => "Andhra Pradesh",
                "pincode" => "522262",
                "address_type" => "office",
                "token" => self::$token
            ]);
        $response->assertStatus(406)->assertJson(['message' => 'Invalid Address Type']);
    }

    /**
     * Successfull Delete Address Test
     * This test is to Delete the Address
     * by using id as credentials
     * 
     * @test
     */
    public function successfulDeleteCartTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/deleteuseraddress', [
                "id" => self::$id,
                "token" => self::$token
            ]);
        $response->assertStatus(200)->assertJson(['message' => 'Address Deleted Successfully']);
    }

    /**
     * UnSuccessfull Delete Address Test
     * This test is to Delete the Address
     * by using id as credentials
     * Using Wrong Credentials for unsuccessful test
     * 
     * @test
     */
    public function unSuccessfulDeleteCartTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/deleteuseraddress', [
                "id" => self::$id,
                "token" => self::$token
            ]);
        $response->assertStatus(404)->assertJson(['message' => 'Address Not Found']);
    }
}
