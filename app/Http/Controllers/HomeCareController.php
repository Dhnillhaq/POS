<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeCareController extends Controller
{
    public function index(){
        return view('products.homecare');
    }
}
