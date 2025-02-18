<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BabyKidController extends Controller
{
    public function index(){
        return view('products.babykid');
    }
}
