<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Validator;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function checkFields($request, $rules)
    {
        $validator = Validator::make($request, $rules);

        if ($validator->fails()) {
            return response()
                ->json(['data' => ['message' => $validator->errors()->first()]], 400)
                ->throwResponse();
        } else {
            return 200;
        }
    }
}
