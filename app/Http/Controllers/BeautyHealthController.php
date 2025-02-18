<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BeautyHealthController extends Controller
{
    public function index(){
        return view('products.beautyhealth');
    }
}
