<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BookControllerTest extends TestCase
{
    protected static $token;
    protected static $id;
    public static function setUpBeforeClass(): void
    {
        self::$id = "21";
        self::$token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODAwMFwvYXBpXC9sb2dpbiIsImlhdCI6MTY1MTg2NzQwMywiZXhwIjoxNjUxODcxMDAzLCJuYmYiOjE2NTE4Njc0MDMsImp0aSI6IjFJeThValZ6eHZWRlpUWHEiLCJzdWIiOjQsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.SG4_kQCPaMKYMTsGxsOs3nH0LzPPH6xYnS8tYX1j3iY";
    }

    /**
     * Successfull Add Book Test
     * This test is to Add a Book to Book Store App
     * by using name, description, author, price and quantity as credentials
     * 
     * @test
     */
    public function successfulAddBookTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/addbook', [
                "name" => "The Place of Illusions by Banerjee",
                "description" => "Yet another retelling of the epic Mahabharata, 
                            this novel has a very interesting narrator. The narrator here is Draupadi, 
                            one of the most misunderstood and wronged characters in the original epic. 
                            This book throws light on her perspectives and emotions and helps us view 
                            the story from a different and more empathetic angle.",
                "author" => "Chitra Banerjee Divakaruni",
                "price" => "100",
                "quantity" => "100",
                "token" => self::$token
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'Book Added Successfully']);
    }

    /**
     * UnSuccessfull Add Book Test
     * This test is to Add a Book to Book Store App
     * by using name, description, author, price and quantity as credentials
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
                "name" => "The Place of Illusions by Banerjee",
                "description" => "Yet another retelling of the epic Mahabharata, 
                            this novel has a very interesting narrator. The narrator here is Draupadi, 
                            one of the most misunderstood and wronged characters in the original epic. 
                            This book throws light on her perspectives and emotions and helps us view 
                            the story from a different and more empathetic angle.",
                "author" => "Chitra Banerjee Divakaruni",
                "price" => "100",
                "quantity" => "100",
                "token" => self::$token
            ]);
        $response->assertStatus(409)->assertJson(['message' => 'Book Already Exits in BookStore']);
    }

    /**
     * Successfull Update Book Test
     * This test is to Update a Book to Book Store App
     * by using name, description, author, price, quantity and id as credentials
     * 
     * @test
     */
    public function successfulUpdateBookTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/updatebook', [
                "id" => self::$id,
                "name" => "The Place of Illusions by Banerjee",
                "description" => "Yet another retelling of the epic Mahabharata, 
                            this novel has a very interesting narrator. The narrator here is Draupadi, 
                            one of the most misunderstood and wronged characters in the original epic. 
                            This book throws light on her perspectives and emotions and helps us view 
                            the story from a different and more empathetic angle.",
                "author" => "Chitra Banerjee Divakaruni",
                "price" => "100",
                "quantity" => "100",
                "token" => self::$token
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'Book Updated Successfully']);
    }

    /**
     * UnSuccessfull Update Book Test
     * This test is to Update a Book to Book Store App
     * by using name, description, author, price, quantity and id as credentials
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
                "id" => "0",
                "name" => "The Place of Illusions by Banerjee",
                "description" => "Yet another retelling of the epic Mahabharata, 
                            this novel has a very interesting narrator. The narrator here is Draupadi, 
                            one of the most misunderstood and wronged characters in the original epic. 
                            This book throws light on her perspectives and emotions and helps us view 
                            the story from a different and more empathetic angle.",
                "author" => "Chitra Banerjee Divakaruni",
                "price" => "100",
                "quantity" => "100",
                "token" => self::$token
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
                "id" => self::$id,
                "quantity" => "20",
                "token" => self::$token
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'Book Quantity Added Successfully']);
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
                "quantity" => "20",
                "token" => self::$token
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
                "id" => self::$id,
                "token" => self::$token
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
                "id" => self::$id,
                "token" => self::$token
            ]);
        $response->assertStatus(404)->assertJson(['message' => 'Book Not Found']);
    }
}
