<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    protected static $token;
    protected static $id;
    public static function setUpBeforeClass(): void
    {
        self::$id = "hn12k94bk";
        self::$token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODAwMFwvYXBpXC9sb2dpbiIsImlhdCI6MTY1MjIxMjYxMywiZXhwIjoxNjUyMjE2MjEzLCJuYmYiOjE2NTIyMTI2MTMsImp0aSI6IkVQcW04bk9Gek85dWtUNjgiLCJzdWIiOjExLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.tq3sFya_IZ4cUKVJNZzwbgA81Ok4SAB-O8BJ9RPMSAg";
    }

    /**
     * Successfull Place Order of an User
     * This test is to Place the Order
     * by using cart_id and address_id as credentials
     * 
     * @test
     */
    public function successfulPlaceOrderTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/placeorder', [
                "cart_id" => "20",
                "address_id" => "10",
                "token" => self::$token
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'Order Placed Successfully']);
    }

    /**
     * UnSuccessfull Place Order of an User
     * This test is to Place the Order
     * by using cart_id and address_id as credentials
     * Using Wrong Credentials for unsuccessful test
     * 
     * @test
     */
    public function unSuccessfulPlaceOrderTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/placeorder', [
                "cart_id" => "20",
                "address_id" => "10",
                "token" => self::$token
            ]);
        $response->assertStatus(409)->assertJson(['message' => 'Already Placed an Order']);
    }

    /**
     * Successfull Cancel Order of an User
     * This test is to Cancel the Order
     * by using order_id as credentials
     * 
     * @test
     */
    public function successfulCancelOrderTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/cancelorder', [
                "order_id" => self::$id,
                "token" => self::$token
            ]);
        $response->assertStatus(200)->assertJson(['message' => 'Order Cancelled Successfully']);
    }

    /**
     * UnSuccessfull Cancel Order of an User
     * This test is to Cancel the Order
     * by using order_id as credentials
     * Using Wrong Credentials for unsuccessful test
     * 
     * @test
     */
    public function unSuccessfulCancelOrderTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/cancelorder', [
                "order_id" => self::$id,
                "token" => self::$token
            ]);
        $response->assertStatus(404)->assertJson(['message' => 'Order Not Found']);
    }
}
