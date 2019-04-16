<?php

namespace App\Models;


class Customer extends BaseModel
{
    //
    protected $fillable = ['id', 'job_title', 'email', 'full_name', 'created_at', 'phone'];


    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
