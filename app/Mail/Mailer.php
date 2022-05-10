<?php

namespace App\Mail;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    public function sendEmail($user, $token)
    {
        $subject = 'Forgot Password';
        //$data = "Hi, " . $user->first_name . " " . $user->last_name . "<br>Your Password Reset Link:<br>http://localhost:8000/resetPassword/" . $token;
        $data = "Hi, " . $user->first_name . " " . $user->last_name . "<br>Your Password Reset Token:<br>" . $token;

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = env('MAIL_HOST');
            $mail->SMTPAuth   = true;
            $mail->Username   = env('MAIL_USERNAME');
            $mail->Password   = env('MAIL_PASSWORD');
            $mail->SMTPSecure = 'tls';
            $mail->Port       = env('MAIL_PORT');
            $mail->setFrom(env('MAIL_USERNAME'), env('MAIL_FROM_NAME'));
            $mail->addAddress($user->email);
            $mail->isHTML(true);
            $mail->Subject =  $subject;
            $mail->Body    = $data;
            if ($mail->send()) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return back()->with('error', 'Message could not be sent.');
        }
    }

    public function sendOrderDetails($order, $book, $cart, $user)
    {
        $subject = 'Order Placed Successfully';
        $data = $user->first_name . " Your Order is Confirmed.<br>" .
            "<br>Your Order Details::" .
            "<br>Order Id: " . $order->order_id .
            "<br>Book Name: " . $book->name .
            "<br>Book Author: " . $book->author .
            "<br>Book Price: " . $book->price .
            "<br>Book Quantity: " . $cart->book_quantity .
            "<br>Total Payment: " . $order->total_price .
            "<br>Save the OrderId For Further Communication." .
            "<br>For Further Querry Contact This Email Id: " . env('MAIL_USERNAME') .
            "<br>Thank you for using our Application!";

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = env('MAIL_HOST');
            $mail->SMTPAuth   = true;
            $mail->Username   = env('MAIL_USERNAME');
            $mail->Password   = env('MAIL_PASSWORD');
            $mail->SMTPSecure = 'tls';
            $mail->Port       = env('MAIL_PORT');
            $mail->setFrom(env('MAIL_USERNAME'), env('MAIL_FROM_NAME'));
            $mail->addAddress($user->email);
            $mail->isHTML(true);
            $mail->Subject =  $subject;
            $mail->Body    = $data;
            if ($mail->send()) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return back()->with('error', 'Message could not be sent.');
        }
    }

    public function sendOrderCancelDetails($order, $book, $cart, $user)
    {
        $subject = 'Order Cancelled Successfully';
        $data = $user->first_name . " Your Order is Cancelled.<br>" .
            "<br>Your Order Details::" .
            "<br>Order Id: " . $order->order_id .
            "<br>Book Name: " . $book->name .
            "<br>Book Author: " . $book->author .
            "<br>Book Price: " . $book->price .
            "<br>Book Quantity: " . $cart->book_quantity .
            "<br>Total Payment: " . $order->total_price .
            "<br>Your Order has been Successfully Cancelled." .
            "<br>For Further Querry Contact This Email Id: " . env('MAIL_USERNAME') .
            "<br>Thank you for using our Application!";

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = env('MAIL_HOST');
            $mail->SMTPAuth   = true;
            $mail->Username   = env('MAIL_USERNAME');
            $mail->Password   = env('MAIL_PASSWORD');
            $mail->SMTPSecure = 'tls';
            $mail->Port       = env('MAIL_PORT');
            $mail->setFrom(env('MAIL_USERNAME'), env('MAIL_FROM_NAME'));
            $mail->addAddress($user->email);
            $mail->isHTML(true);
            $mail->Subject =  $subject;
            $mail->Body    = $data;
            if ($mail->send()) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return back()->with('error', 'Message could not be sent.');
        }
    }
}
