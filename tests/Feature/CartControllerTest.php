<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    protected static $token;
    protected static $id;
    public static function setUpBeforeClass(): void
    {
        self::$id = "13";
        self::$token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODAwMFwvYXBpXC9sb2dpbiIsImlhdCI6MTY1MTk1NDQ2MSwiZXhwIjoxNjUxOTU4MDYxLCJuYmYiOjE2NTE5NTQ0NjEsImp0aSI6IkdQa0NqTDRFN3ZCaGdlMFkiLCJzdWIiOjYsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.Sfzr-PQg-q19uDQ_GMlFK5k56JCPk7SqkcaZ2jkyJlw";
    }

    /**
     * Successfull Add Book to Cart Test
     * This test is to Add a Book to Cart
     * by using book_id and book_quantity as credentials
     * 
     * @test
     */
    public function successfulAddBookToCartTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/addbooktocart', [
                "book_id" => "2",
                "book_quantity" => "10",
                "token" => self::$token
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'Book Added to Cart Successfully']);
    }

    /**
     * UnSuccessfull Add Book to Cart Test
     * This test is to Add a Book to Cart
     * by using book_id and book_quantity as credentials
     * Using Wrong Credentials for unsuccessful test
     * 
     * @test
     */
    public function unSuccessfulAddBookToCartTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/addbooktocart', [
                "book_id" => "2",
                "book_quantity" => "10",
                "token" => self::$token
            ]);
        $response->assertStatus(409)->assertJson(['message' => 'Book Already In Cart']);
    }

    /**
     * Successfull Update Book in the Cart
     * This test is to Update a Book inside the Cart
     * by using cart_id and book_quantity as credentials
     * 
     * @test
     */
    public function successfulUpdateBookInCartTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/updatecart', [
                "id" => self::$id,
                "book_quantity" => "20",
                "token" => self::$token
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'Cart Updated Successfully']);
    }

    /**
     * UnSuccessfull Update Book in the Cart
     * This test is to Update a Book inside the Cart
     * by using cart_id and book_quantity as credentials
     * Using Wrong Credentials for unsuccessful test
     * 
     * @test
     */
    public function unSuccessfulUpdateBookInCartTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/updatecart', [
                "id" => "0",
                "book_quantity" => "20",
                "token" => self::$token
            ]);
        $response->assertStatus(404)->assertJson(['message' => 'Cart Not Found']);
    }

    /**
     * Successfull Delete Cart Test
     * This test is to Delete the cart
     * by using id as credentials
     * 
     * @test
     */
    public function successfulDeleteCartTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/deletecart', [
                "id" => self::$id,
                "token" => self::$token
            ]);
        $response->assertStatus(200)->assertJson(['message' => 'Book Deleted Sucessfully from Cart']);
    }

    /**
     * UnSuccessfull Delete Cart Test
     * This test is to Delete the Cart
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
            ->json('POST', '/api/deletecart', [
                "id" => self::$id,
                "token" => self::$token
            ]);
        $response->assertStatus(404)->assertJson(['message' => 'Cart Not Found']);
    }
}
