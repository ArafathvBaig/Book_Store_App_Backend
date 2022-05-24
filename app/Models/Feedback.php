<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;
    protected $table = 'feedback';
    protected $fillable = [
        'user_id',
        'user_feedback'
    ];

    public static function addFeedback($request, $userId)
    {
        $feedback = new Feedback();
        $feedback->user_id = $userId;
        $feedback->user_feedback = $request->user_feedback;
        $feedback->save();

        return $feedback;
    }

    public static function getFeedback($userId)
    {
        $feedback = Feedback::where('user_id', $userId)->first();

        return $feedback;
    }

    public function users()
    {
        return $this->belongsTo(User::class);
    }
}
