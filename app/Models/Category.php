<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = [];

    public function sub_categories()
    {
        return $this->hasMany(\App\Models\Sub_Category::class);
    }

    public function brands()
    {
        return $this->hasMany(Brand::class);
    }
}
