<?php

namespace App\Http\Controllers;

use App\Services\VisitasBackendService;
use Illuminate\Http\Request;

class CoberturaController extends Controller
{
    private VisitasBackendService $visitasBackend;

    public function __construct(VisitasBackendService $visitasBackend)
    {
        $this->visitasBackend = $visitasBackend;
    }

    /**
     * Mostrar la página de cobertura con filtros
     */
    public function index(Request $request)
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $tipo = $request->input('tipo', 'especialidad');
        $visitadorId = $request->input('visitador_id');

        $resultados = null;

        if ($fechaInicio && $fechaFin) {
            $resultados = $this->calcularCobertura($fechaInicio, $fechaFin, $tipo, $visitadorId);
        }

        // Obtener visitadores para el filtro
        $visitadores = \App\Models\Visitador::orderBy('nombre')->get();

        // Obtener nombre del visitador seleccionado
        $visitadorNombre = 'Todos';
        if ($visitadorId && $visitadorId > 0) {
            $visitador = $visitadores->firstWhere('id', $visitadorId);
            $visitadorNombre = $visitador ? $visitador->nombre : 'Todos';
        }

        return view('cobertura.index', compact(
            'fechaInicio',
            'fechaFin',
            'tipo',
            'visitadorId',
            'visitadorNombre',
            'resultados',
            'visitadores'
        ));
    }

    /**
     * Calcular la cobertura basándose en el tipo (valores fijos)
     */
    private function calcularCobertura(string $fechaInicio, string $fechaFin, string $tipo, ?int $visitadorId = null): array
    {
        // Valores fijos según especificación
        $objetivosDiarios = 7;
        $diasHabiles = 20;
        $base = 150;

        $visitados = match ($tipo) {
            'especialidad' => $this->visitasBackend->obtenerMedicosVisitados($fechaInicio, $fechaFin, $visitadorId),
            'farmacia' => $this->visitasBackend->obtenerFarmaciasVisitadas($fechaInicio, $fechaFin, $visitadorId),
            'hospital' => $this->visitasBackend->obtenerHospitalesVisitados($fechaInicio, $fechaFin, $visitadorId),
            default => [],
        };

        // Para especialidad, el endpoint devuelve datos agrupados por especialidad
        if ($tipo === 'especialidad' && is_array($visitados)) {
            $totalVisitados = 0;
            $detalle = [];

            // Obtener nombre del visitador seleccionado
            $visitadorNombre = 'Todos';
            if ($visitadorId && $visitadorId > 0) {
                $visitador = \App\Models\Visitador::find($visitadorId);
                $visitadorNombre = $visitador ? $visitador->nombre : 'Todos';
            }

            foreach ($visitados as $item) {
                $totalVisitados += $item['medicos_visitados'] ?? 0;
                $detalle[] = [
                    'nombre' => $item['nombre_especialidad'] ?? 'Desconocido',
                    'fecha' => $fechaInicio . ' - ' . $fechaFin,
                    'visitador' => $visitadorNombre,
                ];
            }
        } else {
            $totalVisitados = is_array($visitados) ? count($visitados) : 0;
            $detalle = $visitados;
        }

        $objetivoTotal = $objetivosDiarios * $diasHabiles;
        $cobertura = $objetivoTotal > 0 ? ($totalVisitados / $objetivoTotal) * 100 : 0;

        return [
            'tipo' => $tipo,
            'objetivos_diarios' => $objetivosDiarios,
            'dias_habiles' => $diasHabiles,
            'base' => $base,
            'objetivo_total' => $objetivoTotal,
            'visitados' => $totalVisitados,
            'cobertura' => round($cobertura, 2),
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'detalle' => $detalle,
        ];
    }
}

