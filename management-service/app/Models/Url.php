<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Url extends Model
{
    protected $table = 'urls';
    protected $primaryKey = 'id';
    protected $fillable = [
        'realURL',
        'shortURL'
    ];
    public $timestamps = false;
}
