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
            'name' => 'required|string|max:255|unique:simulado_categories,name',
        ]);
        
        try {
            $cat = SimuladoCategory::create($data);
            return response()->json($cat, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao criar categoria'], 500);
        }
    }

    public function update(Request $request, SimuladoCategory $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:simulado_categories,name,' . $category->id,
        ]);
        
        try {
            $category->update($data);
            return response()->json($category);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao atualizar categoria'], 500);
        }
    }

    public function destroy(SimuladoCategory $category)
    {
        try {
            // Verificar se há simulados usando esta categoria
            $simuladosCount = $category->simulados()->count();
            if ($simuladosCount > 0) {
                return response()->json([
                    'error' => 'Não é possível excluir esta categoria pois existem ' . $simuladosCount . ' simulados associados a ela.'
                ], 422);
            }
            
            $category->delete();
            return response()->json(['deleted' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao excluir categoria'], 500);
        }
    }
}
