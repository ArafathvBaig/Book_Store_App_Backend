<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Tests\TestCase;

class BookControllerTest extends TestCase
{
    protected static $token;
    protected static $id;
    protected static $image;
    public static function setUpBeforeClass(): void
    {
        // Storage::disk('images');
        // self::$image = UploadedFile::fake()->create('Harry Potter and the Half-Blood Prince.jpg');

        // $attachment = 'C:\Users\Arafath Baig\Desktop\BRIDGE LABS\Images\Harry Potter and the Half-Blood Prince.jpg';
        // $storagePath = Storage::disk('attachments')->path('/' . $attachment);
        // self::$image = $storagePath;

        // self::$image = "/C:/Users/Arafath Baig/Desktop/BRIDGE LABS/Images/Harry Potter and the Half-Blood Prince.jpg";

        // self::$image = "Harry Potter and the Half-Blood Prince.jpg";

        // self::$image = ".\public\images\Harry Potter and the Half-Blood Prince.jpg";

        // self::$image = Storage::get('\public\Harry Potter and the Half-Blood Prince.jpg');

        // self::$image = public_path() . "Harry Potter and the Half-Blood Prince.jpg";

        Storage::fake('avatars');
        $file = UploadedFile::fake()->image('avatar.jpg');
        self::$image = $file->hashName();
        self::$id = "25";
        self::$token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvYXBpL2xvZ2luIiwiaWF0IjoxNjUyNzY0OTEyLCJleHAiOjE2NTI3Njg1MTIsIm5iZiI6MTY1Mjc2NDkxMiwianRpIjoiT0NzdGZEQWtsaERhbGE0SSIsInN1YiI6IjUiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.v5J6yPh2DwWKVhS_XFqLSinFd9KV_P0fxNaaiYB_6lA";
    }

    public function test_original_filename_upload()
    {
        $filename = 'logo.jpg';

        $response = $this->post('projects', [
            'name' => 'Some name',
            'logo' => UploadedFile::fake()->image($filename)
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('projects', [
            'name' => 'Some name',
            'logo' => $filename
        ]);
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
                "name" => "Harry Potter Book",
                "description" => "A Series of Harry Potter Books has been Released.",
                "author" => "J.K. Rowling",
                "image" => self::$image,
                "price" => "699.99",
                "quantity" => "1500",
                "token" => self::$token
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
                "name" => "Harry Potter",
                "description" => "A Series of Harry Potter Books has been Released.",
                "author" => "J.K. Rowling",
                "image" => self::$image,
                "price" => "699.99",
                "quantity" => "1500",
                "token" => self::$token
            ]);
        $response->assertStatus(409)->assertJson(['message' => 'Book Already Exits in BookStore']);
    }

    /**
     * Successfull Update Book Test
     * This test is to Update a Book to Book Store App
     * by using name, description, author, image, price, quantity and id as credentials
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
                "name" => "Harry Potter",
                "description" => "A Series of Harry Potter Books has been Released.",
                "author" => "J.K. Rowling",
                "image" => self::$image,
                "price" => "700",
                "quantity" => "1500",
                "token" => self::$token
            ]);
        $response->assertStatus(201)->assertJson(['message' => 'Book Updated Successfully']);
    }

    /**
     * UnSuccessfull Update Book Test
     * This test is to Update a Book to Book Store App
     * by using name, description, author, image, price, quantity and id as credentials
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
                "name" => "Harry Potter",
                "description" => "A Series of Harry Potter Books has been Released.",
                "author" => "J.K. Rowling",
                "image" => self::$image,
                "price" => "700",
                "quantity" => "1500",
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
                "quantity" => "100",
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
