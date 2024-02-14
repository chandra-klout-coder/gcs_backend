<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Event;


class Speaker extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'first_name',
        'middle_name',
        'last_name',
        'about',
        'mobile',
        'email',
        'image_path',
        'designation',
        'company',
        'fb_link',
        'instagram_link',
        'linkedin_link',
        'youtube_link',
        'twitter_link',
        'google_link',
        'skype_link',
        'whatsapp_link'
    ];

    // Define the relationship with the Event model
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
