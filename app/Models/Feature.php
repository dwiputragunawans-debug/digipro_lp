<?php

namespace App\Models;

use App\Models\Concerns\HasIconName;
use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    use HasIconName;

    protected $guarded = [];
}
