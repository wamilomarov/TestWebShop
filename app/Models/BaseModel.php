<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    public static function parseCSV($path, $hasHeader = true)
    {
        $data = array_map('str_getcsv', file(public_path($path)));
        if ($hasHeader)
        {
            array_shift($data); // remove titles
        }
        return $data;
    }
}
