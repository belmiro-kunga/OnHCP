<?php
namespace App\Http\Controllers;

use App\Models\SimuladoCategory;
use Illuminate\Http\Request;

class SimuladoCategoryController extends Controller
{
    public function index()
    {
        return response()->json(SimuladoCategory::orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $cat = SimuladoCategory::create($data);
        return response()->json($cat, 201);
    }
}
