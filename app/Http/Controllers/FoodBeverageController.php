<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FoodBeverageController extends Controller
{
    public function index(){
        return view('products.foodbeverage');
    }
}
