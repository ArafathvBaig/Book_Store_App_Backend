<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BookControllerTest extends TestCase
{
    protected static $token;
    protected static $token1;
    public static function setUpBeforeClass(): void
    {
        self::$token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODAwMFwvYXBpXC9sb2dpbiIsImlhdCI6MTY1MTcyNzcwMCwiZXhwIjoxNjUxNzMxMzAwLCJuYmYiOjE2NTE3Mjc3MDAsImp0aSI6Ikw4NVFZS1pxcHp0a1RCOFAiLCJzdWIiOjksInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.7baiMiRQse8_PLGxLUGikBiH_YYpTE2ItV0hTSfxm3s";
        self::$token1 = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODAwMFwvYXBpXC9sb2dpbiIsImlhdCI6MTY1MTcyNzcwMCwiZXhwIjoxNjUxNzMxMzAwLCJuYmYiOjE2NTE3Mjc3MDAsImp0aSI6Ikw4NVFZS1pxcHp0a1RCOFAiLCJzdWIiOjksInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.7baiMiRQse8_PLGxLUGikBiH_YYpTE2ItV0hTSfxm3s";
    }

    /**
     * Successfull Add Book Test
     * This test is to Add a Book to Book Store App
     * by using name, description, author, image, price and quantity as credentials
     * 
     * @test
     */
    public function successfulAddBookTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/addbook', [
                "name" => "Arafath",
                "description" => "Baig",
                "author" => "1234567890",
                "image" => "arafath@gamil.com",
                "price" => "arafath",
                "quantity" => "arafath"
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'Book Added Successfully']);
    }

    /**
     * UnSuccessfull Add Book Test
     * This test is to Add a Book to Book Store App
     * by using name, description, author, image, price and quantity as credentials
     * Using same name for unsuccessful test
     * 
     * @test
     */
    public function unSuccessfulAddBookTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/addbook', [
                "name" => "Arafath",
                "description" => "Baig",
                "author" => "1234567890",
                "image" => "arafath@gamil.com",
                "price" => "arafath",
                "quantity" => "arafath"
            ]);
        $response->assertStatus(409)->assertJson(['message' => 'Book Already Exits in BookStore']);
    }

    /**
     * Successfull Update Book Test
     * This test is to Update a Book to Book Store App
     * by using name, description, author, image, price and id as credentials
     * 
     * @test
     */
    public function successfulUpdateBookTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/updatebook', [
                "id" => "arafath",
                "name" => "Arafath",
                "description" => "Baig",
                "author" => "1234567890",
                "image" => "arafath@gamil.com",
                "price" => "arafath"
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'Book Updated Successfully']);
    }

    /**
     * UnSuccessfull Update Book Test
     * This test is to Update a Book to Book Store App
     * by using name, description, author, image, price and id as credentials
     * Using Wrong Credentials for unsuccessful test
     * 
     * @test
     */
    public function unSuccessfulUpdateBookTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/updatebook', [
                "id" => "arafath",
                "name" => "Arafath",
                "description" => "Baig",
                "author" => "1234567890",
                "image" => "arafath@gamil.com",
                "price" => "arafath"
            ]);
        $response->assertStatus(404)->assertJson(['message' => 'Book Not Found']);
    }

    /**
     * Successfull Add Quantity Test
     * This test is to Add Quantity to Existing Book
     * by using id and quantity as credentials
     * 
     * @test
     */
    public function successfulAddQuantityTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/addquantity', [
                "id" => "1",
                "quantity" => "20"
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'Book Quantity Updated Successfully']);
    }

    /**
     * UnSuccessfull Add Quantity Test
     * This test is to Add Quantity to Existing Book
     * by using id and quantity as credentials
     * Using Wrong Credentials for unsuccessful test
     * 
     * @test
     */
    public function unSuccessfulAddQuantityTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/addquantity', [
                "id" => "0",
                "quantity" => "20"
            ]);
        $response->assertStatus(404)->assertJson(['message' => 'Book Not Found']);
    }

    /**
     * Successfull Delete Book Test
     * This test is to Delete an Existing Book
     * by using id as credentials
     * 
     * @test
     */
    public function successfulDeleteBookTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/deletebook', [
                "id" => "1"
            ]);
        $response->assertStatus(200)->assertJson(['message' => 'Book Deleted Sucessfully']);
    }

    /**
     * UnSuccessfull Delete Book Test
     * This test is to Delete an Existing Book
     * by using id as credentials
     * Using Wrong Credentials for unsuccessful test
     * 
     * @test
     */
    public function unSuccessfulDeleteBookTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/deletebook', [
                "id" => "1"
            ]);
        $response->assertStatus(404)->assertJson(['message' => 'Book Not Found']);
    }
}
