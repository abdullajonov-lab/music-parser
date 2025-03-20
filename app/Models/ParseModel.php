<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParseModel extends Model
{
    /** @use HasFactory<\Database\Factories\ParseModelFactory> */
    use HasFactory;

    protected $guarded = ["id"];
}
