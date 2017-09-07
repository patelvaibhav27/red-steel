<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
class Book extends Model
{
    protected $fillable = ['title', 'author_name', 'pages_count'];
    // public $table = "books"; // public $table = "<table-name>" ;
    public function cat(){
        return $this->belongsTo('App\Book_cat','book_cats_id');
    }
}