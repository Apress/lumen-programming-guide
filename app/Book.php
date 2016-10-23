<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use Rateable;

    /**
     * The attributes that are mass assignable
     *
     * @var array
     */
    protected $fillable = ['title', 'description', 'author_id'];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function bundles()
    {
        return $this->belongsToMany(\App\Bundle::class);
    }
}
