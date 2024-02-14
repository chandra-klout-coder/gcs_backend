<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Event;

class Agenda extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'image_path',
        'event_id',
        'event_date',
        'start_time',
        'start_minute_time',
        'start_time_type',
        'start_minute_time',
        'end_time',
        'end_time_type'
    ];

    // Define the relationship with the Event model
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
