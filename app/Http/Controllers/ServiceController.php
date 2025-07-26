<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $service = Service::get();
        return response()->json([
            'status' => 'success',
            'data' => ProductResource::collection($service)
        ]);
    }
}
