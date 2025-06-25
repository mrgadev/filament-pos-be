<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $setting = Setting::first();
        if (!$setting) {
            return response()->json([
                'message' => 'Setting not found',
                'success' => false,
            ], 404);
        }
        return response()->json([
            'data' => $setting,
            'message' => 'Setting retrieved successfully',
            'success' => true,
        ], 200);
    }
}