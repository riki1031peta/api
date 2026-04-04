<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClothController extends Controller
{
public function index()
{
    return \App\Models\Cloth::all();
}
}
