<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FeedbackandRatingControllerTest extends TestCase
{
    protected static $token;
    public static function setUpBeforeClass(): void
    {
        self::$token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvYXBpL2xvZ2luIiwiaWF0IjoxNjUzNTkzNTQyLCJleHAiOjE2NTM1OTcxNDIsIm5iZiI6MTY1MzU5MzU0MiwianRpIjoiSkgxMk1lZHNSTkZ3UkNNWCIsInN1YiI6IjYiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.cTLgjoJs7uaLobVD98GfS5qfVuBAgvgOMNYk8d3b2e8";
    }

    /**
     * Successfull Add Feedback of an User
     * This test is to Add Feedback for the Application
     * by using User_feedback as credentials
     * 
     * @test
     */
    public function successfulAddFeedbackTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/addfeedback', [
                "user_feedback" => "Nice App",
                "token" => self::$token
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'FeedBack Added Successfully. Thank You For Your FeedBack.']);
    }

    /**
     * UnSuccessfull Add Feedback of an User
     * This test is to Add Feedback for the Application
     * by using User_feedback as credentials
     * Using Wrong Credentials for unsuccessful test
     * 
     * @test
     */
    public function unSuccessfulAddFeedbackTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/addfeedback', [
                "user_feedback" => "Very Good Application",
                "token" => self::$token
            ]);
        $response->assertStatus(409)->assertJson(['message' => 'You Have Already Given Us a FeedBack. Thank You For Your FeedBack.']);
    }

    /**
     * Successfull Add Rating of an User
     * This test is to Add Rating for a Book
     * by using User_rating and order_id as credentials
     * 
     * @test
     */
    public function successfulAddRatingTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/addrating', [
                "user_rating" => "4",
                "order_id" => "23",
                "token" => self::$token
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'Rating Added Successfully']);
    }

    /**
     * UnSuccessfull Add Rating of an User
     * This test is to Add Rating for a Book
     * by using User_rating and order_id as credentials
     * Using Wrong Credentials for unsuccessful test
     * 
     * @test
     */
    public function unSuccessfulAddRatingTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/addrating', [
                "user_rating" => "4",
                "order_id" => "23",
                "token" => self::$token
            ]);
        $response->assertStatus(409)->assertJson(['message' => 'You Already Gave Rating For Your Order']);
    }
}
