<?php

namespace App\Http\Controllers;

use App\Models\DiscountRule;
use App\Models\Drogeria;
use App\Models\Producto;
use Illuminate\Http\Request;

class DiscountRuleController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        $search = $request->get('search', '');

        $rules = DiscountRule::query()
            ->with(['producto', 'drogueria'])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('producto', function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                        ->orWhere('id', 'like', "%{$search}%");
                });
            })
            ->orderBy('producto_id')
            ->orderByRaw('(drogueria_id IS NULL) DESC')
            ->orderBy('drogueria_id')
            ->paginate(20);

        return view('discount_rules.index', compact('rules', 'search'));
    }

    public function create()
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        $productos = Producto::orderBy('nombre')->get();
        $droguerias = Drogeria::orderBy('nombre')->get();

        return view('discount_rules.create', compact('productos', 'droguerias'));
    }

    public function store(Request $request)
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        $validated = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'drogueria_id' => 'nullable|exists:droguerias,id',

            'min_qty_low' => 'required|integer|min:0',
            'pct_low' => 'required|numeric|min:0|max:100',

            'min_qty_mid' => 'required|integer|min:0',
            'pct_mid' => 'required|numeric|min:0|max:100',

            'min_qty_high' => 'required|integer|min:0',
            'pct_high' => 'required|numeric|min:0|max:100',

            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = (bool)($validated['is_active'] ?? false);

        $productoId = (int)$validated['producto_id'];
        $drogueriaId = $validated['drogueria_id'] ?? null;

        $existsQuery = DiscountRule::query()->where('producto_id', $productoId);
        if ($drogueriaId === null || $drogueriaId === '') {
            $existsQuery->whereNull('drogueria_id');
            $validated['drogueria_id'] = null;
        } else {
            $existsQuery->where('drogueria_id', $drogueriaId);
        }

        if ($existsQuery->exists()) {
            return back()
                ->withInput()
                ->with('error', 'Ya existe una regla para ese producto y esa droguería (o global).');
        }

        DiscountRule::create($validated);

        return redirect()->route('discount_rules.index')
            ->with('success', 'Regla de descuento creada exitosamente.');
    }

    public function edit(DiscountRule $discount_rule)
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        $productos = Producto::orderBy('nombre')->get();
        $droguerias = Drogeria::orderBy('nombre')->get();

        return view('discount_rules.edit', [
            'rule' => $discount_rule,
            'productos' => $productos,
            'droguerias' => $droguerias,
        ]);
    }

    public function update(Request $request, DiscountRule $discount_rule)
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        $validated = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'drogueria_id' => 'nullable|exists:droguerias,id',

            'min_qty_low' => 'required|integer|min:0',
            'pct_low' => 'required|numeric|min:0|max:100',

            'min_qty_mid' => 'required|integer|min:0',
            'pct_mid' => 'required|numeric|min:0|max:100',

            'min_qty_high' => 'required|integer|min:0',
            'pct_high' => 'required|numeric|min:0|max:100',

            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = (bool)($validated['is_active'] ?? false);

        $productoId = (int)$validated['producto_id'];
        $drogueriaId = $validated['drogueria_id'] ?? null;

        $existsQuery = DiscountRule::query()
            ->where('id', '!=', $discount_rule->id)
            ->where('producto_id', $productoId);

        if ($drogueriaId === null || $drogueriaId === '') {
            $existsQuery->whereNull('drogueria_id');
            $validated['drogueria_id'] = null;
        } else {
            $existsQuery->where('drogueria_id', $drogueriaId);
        }

        if ($existsQuery->exists()) {
            return back()
                ->withInput()
                ->with('error', 'Ya existe una regla para ese producto y esa droguería (o global).');
        }

        $discount_rule->update($validated);

        return redirect()->route('discount_rules.index')
            ->with('success', 'Regla de descuento actualizada exitosamente.');
    }

    public function destroy(DiscountRule $discount_rule)
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        $discount_rule->delete();

        return redirect()->route('discount_rules.index')
            ->with('success', 'Regla eliminada exitosamente.');
    }
}
