<?php

namespace App\Http\Controllers;

use App\Models\Visitador;
use Illuminate\Http\Request;

class VisitadorController extends Controller
{
    public function index()
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        $visitadores = Visitador::orderBy('nombre')->get();
        return view('visitadores.index', compact('visitadores'));
    }

    public function create()
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        return view('visitadores.create');
    }

    public function store(Request $request)
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:visitadores,email',
        ]);

        Visitador::create($request->only(['nombre', 'email']));

        return redirect()->route('visitadores.index')
            ->with('success', 'Visitador creado exitosamente.');
    }

    public function edit(Visitador $visitador)
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        return view('visitadores.edit', compact('visitador'));
    }

    public function update(Request $request, Visitador $visitador)
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:visitadores,email,' . $visitador->id,
        ]);

        $visitador->update($request->only(['nombre', 'email']));

        return redirect()->route('visitadores.index')
            ->with('success', 'Visitador actualizado exitosamente.');
    }

    public function destroy(Visitador $visitador)
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        $visitador->delete();

        return redirect()->route('visitadores.index')
            ->with('success', 'Visitador eliminado exitosamente.');
    }
}
