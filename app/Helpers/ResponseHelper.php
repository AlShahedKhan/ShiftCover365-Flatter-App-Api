<?php

namespace App\Helpers;

class ResponseHelper
{
    public static function success($data = [], $message = 'Success', $status = 200)
    {
        return response()->json([
            'success' => true,
            'status_code' => $status,
            'message' => $message,
        ] + $data, $status);
    }

    public static function error($message = 'Something went wrong', $status = 400)
    {
        return response()->json([
            'success' => false,
            'status_code' => $status,
            'message' => $message,
        ], $status);
    }
}
