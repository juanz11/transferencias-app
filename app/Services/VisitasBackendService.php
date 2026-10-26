<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VisitasBackendService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('cobertura.visitas_backend_url', 'http://127.0.0.1:8000');
    }
    
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Obtiene los médicos visitados desde el backend externo
     *
     * @param string $fechaInicio
     * @param string $fechaFin
     * @param int|null $visitadorId
     * @return array
     */
    public function obtenerMedicosVisitados(string $fechaInicio, string $fechaFin, ?int $visitadorId = null): array
    {
        try {
            $params = [
                'from' => $fechaInicio,
                'to' => $fechaFin,
            ];

            if ($visitadorId && $visitadorId > 0) {
                $params['visitador_id'] = $visitadorId;
            }

            // Agregar headers para evitar problemas de CORS
            $response = Http::timeout(30)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->get("{$this->baseUrl}/api/cobertura/medicos-especialidad", $params);

            Log::info('Respuesta de API médicos', [
                'url' => "{$this->baseUrl}/api/estadisticas/medicos-especialidad",
                'params' => $params,
                'status' => $response->status(),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Datos recibidos de API', ['count' => is_array($data) ? count($data) : 0]);
                return $data;
            }

            Log::error('Error al obtener médicos visitados del backend', [
                'status' => $response->status(),
                'body' => $response->body(),
                'headers' => $response->headers(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Excepción al conectar con el backend de visitas', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }

    /**
     * Obtiene farmacias visitadas desde el backend externo
     *
     * @param string $fechaInicio
     * @param string $fechaFin
     * @param int|null $visitadorId
     * @return array
     */
    public function obtenerFarmaciasVisitadas(string $fechaInicio, string $fechaFin, ?int $visitadorId = null): array
    {
        try {
            $params = [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ];

            if ($visitadorId && $visitadorId > 0) {
                $params['visitador_id'] = $visitadorId;
            }

            $response = Http::timeout(30)->get("{$this->baseUrl}/api/farmacias-visitadas", $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Error al obtener farmacias visitadas del backend', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Excepción al conectar con el backend de visitas', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Obtiene hospitales visitados desde el backend externo
     *
     * @param string $fechaInicio
     * @param string $fechaFin
     * @param int|null $visitadorId
     * @return array
     */
    public function obtenerHospitalesVisitados(string $fechaInicio, string $fechaFin, ?int $visitadorId = null): array
    {
        try {
            $params = [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ];

            if ($visitadorId && $visitadorId > 0) {
                $params['visitador_id'] = $visitadorId;
            }

            $response = Http::timeout(30)->get("{$this->baseUrl}/api/hospitales-visitados", $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Error al obtener hospitales visitados del backend', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Excepción al conectar con el backend de visitas', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }
}
