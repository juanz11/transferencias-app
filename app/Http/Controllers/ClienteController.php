<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Drogeria;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        $search = $request->get('search', '');
        
        $clientes = Cliente::with('drogueria')
            ->when($search, function($query) use ($search) {
                $query->where('codigo_cliente', 'like', "%{$search}%")
                      ->orWhere('nombre_cliente', 'like', "%{$search}%");
            })
            ->orderBy('nombre_cliente')
            ->paginate(15);
            
        return view('clientes.index', compact('clientes', 'search'));
    }

    public function create()
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        $droguerias = Drogeria::all();
        return view('clientes.create', compact('droguerias'));
    }

    public function store(Request $request)
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        $request->validate([
            'codigo_cliente' => 'required|string|max:255',
            'nombre_cliente' => 'required|string|max:255',
            'drogueria' => 'required|exists:droguerias,id'
        ]);

        Cliente::create($request->all());

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente creado exitosamente.');
    }

    public function show(Cliente $cliente)
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        return view('clientes.show', compact('cliente'));
    }

    public function edit(Cliente $cliente)
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        $droguerias = Drogeria::all();
        return view('clientes.edit', compact('cliente', 'droguerias'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        $request->validate([
            'codigo_cliente' => 'required|string|max:255',
            'nombre_cliente' => 'required|string|max:255',
            'drogueria' => 'required|exists:droguerias,id'
        ]);

        $cliente->update($request->all());

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente actualizado exitosamente.');
    }

    public function destroy(Cliente $cliente)
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        $cliente->delete();

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente eliminado exitosamente.');
    }
}
