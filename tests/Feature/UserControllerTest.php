<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    protected static $reset;
    protected static $token;
    public static function setUpBeforeClass(): void
    {
        self::$reset = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3RcL2FwaVwvZm9yZ290cGFzc3dvcmQiLCJpYXQiOjE2NTE3NzQ5MDgsImV4cCI6MTY1MTc3ODUwOCwibmJmIjoxNjUxNzc0OTA4LCJqdGkiOiJ0T0Q5ajZzMGRheGoyYWd6Iiwic3ViIjozLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.JzPDC8gyz72e-SwvBjPL83KUmtI9IYO6HBPWOGIW5gw";
        self::$token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODAwMFwvYXBpXC9sb2dpbiIsImlhdCI6MTY1MTcyNzcwMCwiZXhwIjoxNjUxNzMxMzAwLCJuYmYiOjE2NTE3Mjc3MDAsImp0aSI6Ikw4NVFZS1pxcHp0a1RCOFAiLCJzdWIiOjksInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.7baiMiRQse8_PLGxLUGikBiH_YYpTE2ItV0hTSfxm3s";
    }

    /**
     * Successfull Registration
     * This test is to check user Registered Successfully or not
     * by using first_name, last_name, phone_number, role, email and password as credentials
     * 
     * @test
     */
    public function successfulRegistrationTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/register', [
                "first_name" => "Arafath",
                "last_name" => "Baig",
                "phone_number" => "1234567890",
                "email" => "arafath@gamil.com",
                "password" => "arafath",
                "password_confirmation" => "arafath",
                "role" => ""
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'User Successfully Registered']);
    }

    /**
     * Test to check the user is already registered
     * by using first_name, last_name, phone_number, role, email and password as credentials
     * The email used is a registered email for this test
     * 
     * @test
     */
    public function userisAlreadyRegisteredTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])
            ->json('POST', '/api/register', [
                "first_name" => "Arafath",
                "last_name" => "Baig",
                "phone_number" => "1234567890",
                "email" => "arafath@gamil.com",
                "password" => "arafath",
                "password_confirmation" => "arafath",
                "role" => ""
            ]);
        $response->assertStatus(401)->assertJson(['message' => 'The email has already been taken']);
    }

    /**
     * Test for successful Login
     * Login the user by using the email and password as credentials
     * 
     * @test
     */
    public function successfulLoginTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])->json(
            'POST',
            '/api/login',
            [
                "email" => "arafath@gamil.com",
                "password" => "arafath"
            ]
        );
        $response->assertStatus(200)->assertJson(['success' => 'Login Successful']);
    }

    /**
     * Test for Unsuccessfull Login
     * Login the user by email and password
     * Wrong password for this test
     * 
     * @test
     */
    public function unSuccessfulLoginTest()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])->json(
            'POST',
            '/api/login',
            [
                "email" => "arafath@gamil.com",
                "password" => "arafathbaig"
            ]
        );
        $response->assertStatus(402)->assertJson(['message' => 'Wrong Password']);
    }

    // /**
    //  * Test for Successfull Logout
    //  * Logout a user using the token generated at login
    //  * 
    //  * @test
    //  */
    // public function successfulLogoutTest()
    // { {
    //         $response = $this->withHeaders([
    //             'Content-Type' => 'Application/json',
    //         ])->json('POST', '/api/logout', [
    //             "token" => self::$token
    //         ]);

    //         $response->assertStatus(200)->assertJson(['message' => 'User Successfully Logged Out']);
    //     }
    // }

    // /**
    //  * Test for unSuccessfull Logout
    //  * Logout a user using the token generated at login
    //  * Passing the wrong token for this test
    //  * 
    //  * @test
    //  */
    // public function unsuccessfulLogoutTest()
    // { {
    //         $response = $this->withHeaders([
    //             'Content-Type' => 'Application/json',
    //         ])->json('POST', '/api/logout', [
    //             "token" => self::$token
    //         ]);

    //         $response->assertStatus(401)->assertJson(['message' => 'Invalid Authorization Token']);
    //     }
    // }

    /**
     * Test for Successfull Forgot Password
     * Send a mail for forgot password of a registered user
     * 
     * @test
     */
    public function successfulForgotPasswordTest()
    { {
            $response = $this->withHeaders([
                'Content-Type' => 'Application/json',
            ])->json('POST', '/api/forgotpassword', [
                "email" => "arafathbaig1997@gmail.com"
            ]);

            $response->assertStatus(201)->assertJson(['message' => 'Reset Password Token Sent to your Email']);
        }
    }

    /**
     * Test for UnSuccessfull Forgot Password
     * Send a mail for forgot password of a registered user
     * Non-Registered email for this test
     * 
     * @test
     */
    public function unsuccessfulForgotPasswordTest()
    { {
            $response = $this->withHeaders([
                'Content-Type' => 'Application/json',
            ])->json('POST', '/api/forgotpassword', [
                "email" => "arafathbaig123@gmail.com"
            ]);

            $response->assertStatus(404)->assertJson(['message' => 'Not a Registered Email']);
        }
    }

    /**
     * Test for Successfull Reset Password
     * Reset password using the token and 
     * setting the new password to be the password
     * 
     * @test
     */
    public function successfulResetPasswordTest()
    { {
            $response = $this->withHeaders([
                'Content-Type' => 'Application/json',
            ])->json('POST', '/api/resetpassword', [
                "new_password" => "arafathbaig1997",
                "password_confirmation" => "arafathbaig1997",
                "token" => self::$reset
            ]);

            $response->assertStatus(200)->assertJson(['message' => 'Password Reset Successful']);
        }
    }

    /**
     * Test for unSuccessfull Reset Password
     * Reset password using the token and 
     * setting the new password to be the password
     * Wrong token is passed for this test
     * 
     * @test
     */
    public function unsuccessfulResetPasswordTest()
    { {
            $response = $this->withHeaders([
                'Content-Type' => 'Application/json',
            ])->json('POST', '/api/resetpassword', [
                "new_password" => "arafath1234",
                "password_confirmation" => "arafath1234",
                "token" => self::$token
            ]);

            $response->assertStatus(401)->assertJson(['message' => 'Invalid Authorization Token']);
        }
    }
}
