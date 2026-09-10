<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Charla;
use App\Models\Cliente;
use App\Models\Visitador;

class CharlaController extends Controller
{
    // ===================== ADMIN =====================

    public function index()
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        $charlas = Charla::with(['cliente', 'visitador'])->orderBy('fecha', 'desc')->paginate(20);
        return view('admin.charlas.index', compact('charlas'));
    }

    public function create()
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        $clientes = Cliente::orderBy('nombre_cliente')->get(['id', 'nombre_cliente', 'zona']);
        $visitadores = Visitador::orderBy('nombre')->get();
        return view('admin.charlas.create', compact('clientes', 'visitadores'));
    }

    public function store(Request $request)
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        $request->validate([
            'fecha' => 'required|date',
            'cliente_id' => 'required|exists:clientes,id',
            'zona' => 'nullable|string|max:255',
            'cantidad_charlas' => 'required|integer|min:0',
            'participantes' => 'required|integer|min:0',
            'visitador_id' => 'nullable|exists:visitadores,id',
        ]);

        Charla::create($request->all());

        return redirect()->route('admin.charlas.index')->with('success', 'Charla registrada correctamente.');
    }

    public function edit(Charla $charla)
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        $clientes = Cliente::orderBy('nombre_cliente')->get(['id', 'nombre_cliente', 'zona']);
        $visitadores = Visitador::orderBy('nombre')->get();
        return view('admin.charlas.edit', compact('charla', 'clientes', 'visitadores'));
    }

    public function update(Request $request, Charla $charla)
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        $request->validate([
            'fecha' => 'required|date',
            'cliente_id' => 'required|exists:clientes,id',
            'zona' => 'nullable|string|max:255',
            'cantidad_charlas' => 'required|integer|min:0',
            'participantes' => 'required|integer|min:0',
            'visitador_id' => 'nullable|exists:visitadores,id',
        ]);

        $charla->update($request->all());

        return redirect()->route('admin.charlas.index')->with('success', 'Charla actualizada correctamente.');
    }

    public function destroy(Charla $charla)
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        $charla->delete();

        return redirect()->route('admin.charlas.index')->with('success', 'Charla eliminada correctamente.');
    }
}
