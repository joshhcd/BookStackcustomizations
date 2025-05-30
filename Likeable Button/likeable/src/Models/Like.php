<?php
namespace BookStack\Likeable\Models;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    protected $fillable = ['user_id'];

    public function likeable()
    {
        return $this->morphTo();
    }
}
