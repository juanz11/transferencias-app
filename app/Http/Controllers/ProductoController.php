<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        $search = $request->get('search', '');
        
        $productos = Producto::when($search, function($query) use ($search) {
                $query->where('nombre', 'like', "%{$search}%")
                      ->orWhere('id', 'like', "%{$search}%");
            })
            ->orderBy('nombre')
            ->paginate(15);
            
        return view('productos.index', compact('productos', 'search'));
    }

    public function create()
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        return view('productos.create');
    }

    public function store(Request $request)
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }
        
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'comision' => 'required|numeric|min:0|max:100',
        ]);
        
        Producto::create($validated);
        
        return redirect()->route('productos.index')
            ->with('success', 'Producto creado exitosamente.');
    }

    public function edit(Producto $producto)
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        return view('productos.edit', compact('producto'));
    }

    public function update(Request $request, Producto $producto)
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'comision' => 'required|numeric|min:0|max:100',
        ]);

        $producto->update($validated);

        return redirect()->route('productos.index')
            ->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy(Producto $producto)
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        $producto->delete();

        return redirect()->route('productos.index')
            ->with('success', 'Producto eliminado exitosamente.');
    }
}
