<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Variation;
use App\Models\Addon;

class PosController extends Controller
{
    public function index()
    {
        $variations = Variation::all();
        $addons = Addon::all();
        return view('pos.index', compact('variations', 'addons'));
    }
}
