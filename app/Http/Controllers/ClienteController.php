<?php
// app/Http/Controllers/ClienteController.php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Vehiculo;
use App\Models\Servicio;
use App\Models\Usuario;
use App\Models\DiaNoLaborable;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClienteController extends Controller
{
    public function dashboard(): View
    {
        $user = Auth::user();

        if (!$user || !$user->isCliente()) {
            abort(403, 'Acceso no autorizado');
        }

        try {
            $vehiculos = $user->vehiculos()
                ->withCount('citas')
                ->orderByDesc('citas_count')
                ->get();

            $vehiculosDashboard = $vehiculos->take(3);

            // Obtener todas las citas del usuario
            $citas = $user->citas()
                ->with(['vehiculo', 'servicios'])
                ->orderBy('fecha_hora', 'desc')
                ->get();

            // PRÓXIMAS CITAS - SOLO CONFIRMADAS 
            $proximas_citas = $citas->where('estado', 'confirmada')
                ->where('fecha_hora', '>=', now()) // Solo futuras
                ->sortBy('fecha_hora');

            // HISTORIAL - SOLO CANCELADAS O FINALIZADAS
            $historial_citas = $citas->filter(function ($cita) {
                return in_array($cita->estado, ['finalizada', 'cancelada']);
            });
            return view('cliente.dashboard', [
                'user' => $user,
                'stats' => [
                    'total_vehiculos' => $vehiculos->count(),
                    'total_citas' => $citas->count(),
                    'citas_pendientes' => $citas->where('estado', 'pendiente')->count(),
                    'citas_confirmadas' => $citas->where('estado', 'confirmada')->count(),
                ],
                'mis_vehiculos' => $vehiculos,
                'vehiculos_dashboard' => $vehiculosDashboard,
                'proximas_citas' => $proximas_citas->take(5),
                'historial_citas' => $historial_citas->take(5),
                'notificaciones' => $user->notificaciones()->orderBy('fecha_envio', 'desc')->get(),
                'notificacionesNoLeidas' => $user->notificaciones()->where('leido', false)->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Dashboard error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('cliente.dashboard', [
                'user' => $user,
                'stats' => [
                    'total_vehiculos' => 0,
                    'total_citas' => 0,
                    'citas_pendientes' => 0,
                    'citas_confirmadas' => 0,
                ],
                'mis_vehiculos' => collect(),
                'mis_citas' => collect(),
                'notificaciones' => collect(),
                'notificacionesNoLeidas' => 0
            ]);
        }
    }

    public function vehiculos(): View
    {

        $vehiculos = Vehiculo::where('usuario_id', Auth::id())
            ->with('citas')
            ->get();


        return view('VehiculosViews.index', compact('vehiculos'));
    }

    public function citas(Request $request): View
    {
        $query = Cita::where('usuario_id', Auth::id())
            ->with(['vehiculo', 'servicios']);

        // Aplicar filtros
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_hora', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_hora', '<=', $request->fecha_hasta);
        }

        if ($request->filled('vehiculo_id')) {
            $query->where('vehiculo_id', $request->vehiculo_id);
        }

        // Ordenar por fecha más reciente primero
        $citas = $query->orderBy('fecha_hora', 'desc')->paginate(10);

        // Mantener los parámetros de filtro en la paginación
        $citas->appends($request->query());

        return view('cliente.citas', compact('citas'));
    }

    public function misVehiculosAjax()
    {
        $vehiculos = Auth::user()->vehiculos()
            ->withCount('citas')
            ->orderByDesc('citas_count')
            ->take(3)
            ->get();

        return response()->json(['vehiculos' => $vehiculos]);
    }

    public function checkStatus()
    {
        $user = Auth::user();
        return response()->json(['is_active' => $user->estado]);
    }

    public function storeCita(Request $request)
    {
        try {
            // Validar estado del usuario
            if (!Auth::user()->estado) {
                throw new \Exception('Tu cuenta está inactiva.', 403);
            }

            $validated = $request->validate([
                'vehiculo_id' => 'required|exists:vehiculos,id,usuario_id,' . Auth::id(),
                'fecha' => 'required|date|after_or_equal:today',
                'hora' => 'required|date_format:H:i',
                'servicios' => 'required|array|min:1',
                'servicios.*' => 'exists:servicios,id',
                'observaciones' => 'nullable|string|max:500'
            ]);

            // Combinar fecha y hora
            $fechaCita = Carbon::createFromFormat(
                'Y-m-d H:i',
                $validated['fecha'] . ' ' . $validated['hora'],
                config('app.timezone') // America/El_Salvador
            );

            // Validar que la fecha y hora no sean en el pasado
            if ($fechaCita->lt(now())) {
                throw new \Exception('No puedes agendar citas en fechas u horas pasadas.', 400);
            }

            // Validar que no sea domingo
            if ($fechaCita->isSunday()) {
                throw new \Exception('No atendemos domingos.', 400);
            }

            // Validar 1 mes de anticipación
            if ($fechaCita->gt(Carbon::now()->addMonth())) {
                throw new \Exception('Máximo 1 mes de anticipación.', 400);
            }

            // Validar día no laborable
            if (DiaNoLaborable::whereDate('fecha', $fechaCita)->exists()) {
                throw new \Exception('Día no laborable.', 400);
            }

            // Validar horario laboral (8AM a 6PM)
            $hora = $fechaCita->format('H:i');
            if ($hora < '08:00' || $hora > '18:00') {
                throw new \Exception('Horario no laboral (8:00 AM - 6:00 PM).', 400);
            }

            // Validar tipo de vehículo vs servicios
            $vehiculo = Vehiculo::find($validated['vehiculo_id']);
            $servicios = Servicio::whereIn('id', $validated['servicios'])->get();

            foreach ($servicios as $servicio) {
                if ($servicio->categoria !== $vehiculo->tipo) {
                    throw new \Exception('El servicio "' . $servicio->nombre . '" no está disponible para ' . $vehiculo->tipo . 's.', 400);
                }
            }

            // Calcular duración total
            $duracionTotal = $servicios->sum('duracion_min');
            $horaFin = $fechaCita->copy()->addMinutes($duracionTotal);

            // Verificar horario de cierre
            if ($horaFin->format('H:i') > '18:00') {
                throw new \Exception('Los servicios seleccionados exceden el horario de cierre.', 400);
            }

            // Verificar colisión con otras citas (excluir cita actual si existe)
            $citasQuery = Cita::where('estado', '!=', 'cancelada')
                ->where(function ($query) use ($fechaCita, $horaFin) {
                    $query->whereBetween('fecha_hora', [$fechaCita, $horaFin])
                        ->orWhere(function ($q) use ($fechaCita, $horaFin) {
                            $q->where('fecha_hora', '<', $fechaCita)
                                ->whereRaw('DATE_ADD(fecha_hora, INTERVAL (
                                SELECT SUM(servicios.duracion_min) 
                                FROM cita_servicio 
                                JOIN servicios ON cita_servicio.servicio_id = servicios.id 
                                WHERE cita_servicio.cita_id = citas.id
                            ) MINUTE) > ?', [$fechaCita]);
                        })
                        ->orWhere(function ($q) use ($fechaCita, $horaFin) {
                            $q->where('fecha_hora', '>', $fechaCita)
                                ->where('fecha_hora', '<', $horaFin);
                        });
                });

            // Excluir la cita actual si se está editando
            if ($request->has('cita_id') && $request->cita_id) {
                $citasQuery->where('id', '!=', $request->cita_id);
            }

            $citasSuperpuestas = $citasQuery->exists();

            if ($citasSuperpuestas) {
                $horariosDisponibles = $this->getAvailableTimes($fechaCita->format('Y-m-d'), $request->cita_id);
                return response()->json([
                    'success' => false,
                    'message' => 'El horario seleccionado está ocupado.',
                    'data' => [
                        'available_times' => $horariosDisponibles,
                        'duracion_total' => $duracionTotal
                    ]
                ], 409);
            }

            DB::beginTransaction();

            $cita = Cita::create([
                'usuario_id' => Auth::id(),
                'vehiculo_id' => $validated['vehiculo_id'],
                'fecha_hora' => $fechaCita,
                'estado' => Cita::ESTADO_PENDIENTE,
                'observaciones' => $validated['observaciones'] ?? null
            ]);

            $serviciosConPrecio = $servicios->mapWithKeys(function ($servicio) {
                return [$servicio->id => [
                    'precio' => $servicio->precio
                ]];
            });

            $cita->servicios()->attach($serviciosConPrecio);

            // Notificación
            $cita->usuario->notificaciones()->create([
                'titulo' => 'Cita agendada',
                'mensaje' => 'Tu cita para el ' . $fechaCita->format('d/m/Y H:i') . ' ha sido agendada.',
                'tipo' => 'confirmacion',
                'fecha_envio' => now(),
                'leido' => false
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cita creada exitosamente',
                'data' => [
                    'cita_id' => $cita->id,
                    'fecha_hora' => $fechaCita->format('Y-m-d H:i:s'),
                    'servicios_count' => count($validated['servicios']),
                    'servicios_nombres' => $servicios->pluck('nombre')->join(', '),
                    'duracion_total' => $duracionTotal,
                    'hora_fin' => $horaFin->format('H:i'),
                    'vehiculo_marca' => $vehiculo->marca,
                    'vehiculo_modelo' => $vehiculo->modelo,
                    'vehiculo_placa' => $vehiculo->placa
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear cita: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
                'error' => $e->getTraceAsString()
            ]);

            $statusCode = is_int($e->getCode()) && $e->getCode() >= 100 && $e->getCode() < 600
                ? $e->getCode()
                : 400;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_type' => get_class($e)
            ], $statusCode);
        }
    }
    private function getAvailableTimes($date, $excludeCitaId = null)
    {
        // Crear fecha usando Carbon sin problemas de timezone
        $date = Carbon::createFromFormat('Y-m-d', $date, config('app.timezone'))->startOfDay();

        // Obtener día de la semana ISO (1=Lunes, 7=Domingo)
        $dayOfWeekISO = $date->dayOfWeekIso;

        Log::info("getAvailableTimes:", [
            'fecha' => $date->toDateString(),
            'dia_semana_iso' => $dayOfWeekISO,
            'nombre_dia' => $date->locale('es')->dayName,
            'exclude_cita_id' => $excludeCitaId
        ]);

        // Verificar si es domingo (ISO = 7)
        if ($dayOfWeekISO === 7) {
            Log::info("Domingo detectado, no hay horarios disponibles");
            return [];
        }

        // Verificar días no laborables
        if (DiaNoLaborable::whereDate('fecha', $date)->exists()) {
            Log::info("Día no laborable detectado");
            return [];
        }

        // Obtener horarios ocupados excluyendo cita específica
        $query = Cita::whereDate('fecha_hora', $date)
            ->where('estado', '!=', 'cancelada')
            ->with('servicios');

        if ($excludeCitaId) {
            $query->where('id', '!=', $excludeCitaId);
            Log::info("Excluyendo cita ID: {$excludeCitaId}");
        }

        $horariosOcupados = $query->get()->map(function ($cita) {
            $horaInicio = \Carbon\Carbon::parse($cita->fecha_hora);
            $duracionTotal = $cita->servicios->sum('duracion_min');
            return [
                'inicio' => $horaInicio,
                'fin' => $horaInicio->copy()->addMinutes($duracionTotal)
            ];
        });

        // Obtener horarios programados para este día ISO
        $horariosDisponibles = \App\Models\Horario::where('dia_semana', $dayOfWeekISO)
            ->where('activo', true)
            ->get();

        Log::info("Horarios programados para día ISO {$dayOfWeekISO}:", [
            'count' => $horariosDisponibles->count(),
            'horarios' => $horariosDisponibles->pluck('hora_inicio', 'hora_fin')->toArray()
        ]);

        if ($horariosDisponibles->isEmpty()) {
            Log::info("No hay horarios programados para este día");
            return [];
        }

        // Generar horarios disponibles
        $horariosLibres = [];

        foreach ($horariosDisponibles as $horario) {
            $horaActual = $date->copy()
                ->setTimezone(config('app.timezone'))
                ->setTimeFromTimeString($horario->hora_inicio->format('H:i:s'));
            $horaCierre = $date->copy()->setTimeFromTimeString($horario->hora_fin->format('H:i:s'));

            while ($horaActual->lt($horaCierre)) {
                $horaFin = $horaActual->copy()->addMinutes(30);

                // Verificar si hay colisión con horarios ocupados
                $disponible = true;
                foreach ($horariosOcupados as $ocupado) {
                    if ($horaActual->lt($ocupado['fin']) && $horaFin->gt($ocupado['inicio'])) {
                        $disponible = false;
                        break;
                    }
                }

                if ($disponible && $horaFin->lte($horaCierre)) {
                    $horariosLibres[] = $horaActual->format('H:i');
                }

                $horaActual->addMinutes(30);
            }
        }

        // Si la fecha es hoy, filtrar horarios que ya pasaron
        if ($date->isToday()) {
            $horaActual = \Carbon\Carbon::now();
            $horariosLibres = array_filter($horariosLibres, function ($hora) use ($horaActual) {
                $horaCita = \Carbon\Carbon::createFromFormat('H:i', $hora);
                return $horaCita->gt($horaActual);
            });

            // Reindexar el array
            $horariosLibres = array_values($horariosLibres);

            Log::info("Horarios disponibles después de filtrar los pasados para hoy:", [
                'count' => count($horariosLibres),
                'horarios' => $horariosLibres
            ]);
        }

        Log::info("Horarios libres generados:", [
            'count' => count($horariosLibres),
            'horarios' => $horariosLibres
        ]);

        return $horariosLibres;
    }
    public function cancelarCita(Cita $cita)
    {
        // Verificar que la cita pertenece al usuario
        if ($cita->usuario_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para cancelar esta cita'
            ], 403);
        }

        // Estados permitidos para cancelación
        $estadosCancelables = [Cita::ESTADO_PENDIENTE, Cita::ESTADO_CONFIRMADA];

        if (!in_array($cita->estado, $estadosCancelables)) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden cancelar citas en estado: ' .
                    implode(' o ', array_map(function ($estado) {
                        return Cita::getEstados()[$estado];
                    }, $estadosCancelables))
            ], 400);
        }

        try {
            DB::beginTransaction();

            $fechaOriginal = $cita->fecha_hora->format('d/m/Y H:i');
            $cita->estado = Cita::ESTADO_CANCELADA;
            $cita->save();

            // Crear notificación
            $cita->usuario->notificaciones()->create([
                'titulo' => 'Cita cancelada',
                'mensaje' => "Tu cita para el {$fechaOriginal} ha sido cancelada.",
                'tipo' => 'cancelacion',
                'fecha_envio' => now(),
                'leido' => false
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cita cancelada exitosamente',
                'cita_id' => $cita->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al cancelar cita', [
                'cita_id' => $cita->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar la cita: ' . $e->getMessage()
            ], 500);
        }
    }
    public function getDashboardData()
    {
        try {
            $user = Auth::user();

            if (!$user || !$user->isCliente()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acceso no autorizado'
                ], 403);
            }

            // Obtener datos
            $vehiculos = $user->vehiculos()
                ->withCount('citas')
                ->orderByDesc('citas_count')
                ->get();

            // Obtener todas las citas del usuario
            $todasLasCitas = $user->citas()
                ->with(['vehiculo', 'servicios'])
                ->orderBy('fecha_hora', 'desc')
                ->get();

            // PRÓXIMAS CITAS - SOLO CONFIRMADAS
            $proximas_citas = $todasLasCitas->where('estado', 'confirmada')
                ->where('fecha_hora', '>=', now())
                ->sortBy('fecha_hora')
                ->values();

            // HISTORIAL - SOLO CANCELADAS O FINALIZADAS
            $historial_citas = $todasLasCitas->filter(function ($cita) {
                return in_array($cita->estado, ['cancelada', 'finalizada']);
            })->values();

            return response()->json([
                'success' => true,
                'proximas_citas' => $proximas_citas,
                'historial_citas' => $historial_citas,
                'stats' => [
                    'total_vehiculos' => $vehiculos->count(),
                    'total_citas' => $todasLasCitas->count(),
                    'citas_pendientes' => $todasLasCitas->where('estado', 'pendiente')->count(),
                    'citas_confirmadas' => $todasLasCitas->where('estado', 'confirmada')->count(),
                ],
                'notificacionesNoLeidas' => $user->notificaciones()->where('leido', false)->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar datos del dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function edit(Cita $cita)
    {
        if ($cita->usuario_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para editar esta cita'
            ], 403);
        }

        if (!in_array($cita->estado, ['pendiente', 'confirmada'])) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden editar citas en estado pendiente o confirmada'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'cita' => $cita->load(['vehiculo', 'servicios']),
                'vehiculo_id' => $cita->vehiculo_id,
                'servicios' => $cita->servicios->pluck('id')->toArray(),
                'fecha' => $cita->fecha_hora->format('Y-m-d'),
                'hora' => $cita->fecha_hora->format('H:i'),
                'observaciones' => $cita->observaciones,
                'vehiculo_tipo' => $cita->vehiculo->tipo
            ]
        ]);
    }

    public function updateCita(Request $request, Cita $cita)
    {
        // Verificar permisos
        if ($cita->usuario_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para editar esta cita'
            ], 403);
        }

        if (!in_array($cita->estado, ['pendiente', 'confirmada'])) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden editar citas en estado pendiente o confirmada'
            ], 400);
        }

        try {
            $validated = $request->validate([
                'vehiculo_id' => 'required|exists:vehiculos,id,usuario_id,' . Auth::id(),
                'fecha' => 'required|date|after_or_equal:today',
                'hora' => 'required|date_format:H:i',
                'servicios' => 'required|array|min:1',
                'servicios.*' => 'exists:servicios,id',
                'observaciones' => 'nullable|string|max:500'
            ]);

            // Bloquear cambios de fecha/hora/vehículo si faltan menos de 24 horas para cita confirmada
            $fechaActual = now();
            $fechaCitaOriginal = Carbon::parse($cita->fecha_hora);
            $horasRestantes = $fechaActual->diffInHours($fechaCitaOriginal, false);

            if ($cita->estado === 'confirmada' && $horasRestantes < 24) {
                // Verificar qué campos intentan modificar
                $fechaCitaNueva = Carbon::parse($validated['fecha'] . ' ' . $validated['hora']);
                $haCambiadoFechaHora = !$fechaCitaOriginal->equalTo($fechaCitaNueva);
                $haCambiadoVehiculo = $cita->vehiculo_id != $validated['vehiculo_id'];

                if ($haCambiadoFechaHora || $haCambiadoVehiculo) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No puedes cambiar la fecha, hora o vehículo cuando faltan menos de 24 horas para la cita confirmada. Solo puedes modificar servicios u observaciones.'
                    ], 400);
                }
            }

            // Crear request temporal para usar storeCita con exclusión
            $tempRequest = new Request();
            $tempRequest->replace($validated);
            $tempRequest->merge(['cita_id' => $cita->id]); // Agregar ID para exclusión

            DB::beginTransaction();

            // Combinar fecha y hora
            $fechaCita = Carbon::parse($validated['fecha'] . ' ' . $validated['hora']);

            // Verificar si la fecha/hora ha cambiado
            $fechaOriginal = Carbon::parse($cita->fecha_hora);
            $haCambiadoFecha = !$fechaOriginal->equalTo($fechaCita);

            // Si la cita estaba confirmada y cambió la fecha, cambiar a pendiente
            $nuevoEstado = $cita->estado;
            if ($cita->estado === 'confirmada' && $haCambiadoFecha) {
                $nuevoEstado = 'pendiente';
            }

            // Validar que la nueva fecha/hora no sea en el pasado
            if ($fechaCita->lt(now())) {
                throw new \Exception('No puedes cambiar la cita a una fecha u hora pasada.', 400);
            }

            // Validaciones básicas
            if ($fechaCita->isSunday()) {
                throw new \Exception('No atendemos domingos.', 400);
            }

            if ($fechaCita->gt(Carbon::now()->addMonth())) {
                throw new \Exception('Máximo 1 mes de anticipación.', 400);
            }

            if (DiaNoLaborable::whereDate('fecha', $fechaCita)->exists()) {
                throw new \Exception('Día no laborable.', 400);
            }

            $hora = $fechaCita->format('H:i');
            if ($hora < '08:00' || $hora > '18:00') {
                throw new \Exception('Horario no laboral (8:00 AM - 6:00 PM).', 400);
            }

            // Validar tipo de vehículo vs servicios
            $vehiculo = Vehiculo::find($validated['vehiculo_id']);
            $servicios = Servicio::whereIn('id', $validated['servicios'])->get();

            foreach ($servicios as $servicio) {
                if ($servicio->categoria !== $vehiculo->tipo) {
                    throw new \Exception('El servicio "' . $servicio->nombre . '" no está disponible para ' . $vehiculo->tipo . 's.', 400);
                }
            }

            // Calcular duración total
            $duracionTotal = $servicios->sum('duracion_min');
            $horaFin = $fechaCita->copy()->addMinutes($duracionTotal);

            if ($horaFin->format('H:i') > '18:00') {
                throw new \Exception('Los servicios seleccionados exceden el horario de cierre.', 400);
            }

            // Verificar colisión excluyendo esta cita
            $citasSuperpuestas = Cita::where('estado', '!=', 'cancelada')
                ->where('id', '!=', $cita->id) // EXCLUSIÓN EXPLÍCITA
                ->where(function ($query) use ($fechaCita, $horaFin) {
                    $query->whereBetween('fecha_hora', [$fechaCita, $horaFin])
                        ->orWhere(function ($q) use ($fechaCita, $horaFin) {
                            $q->where('fecha_hora', '<', $fechaCita)
                                ->whereRaw('DATE_ADD(fecha_hora, INTERVAL (
                        SELECT SUM(servicios.duracion_min) 
                        FROM cita_servicio 
                        JOIN servicios ON cita_servicio.servicio_id = servicios.id 
                        WHERE cita_servicio.cita_id = citas.id
                    ) MINUTE) > ?', [$fechaCita]);
                        })
                        ->orWhere(function ($q) use ($fechaCita, $horaFin) {
                            $q->where('fecha_hora', '>', $fechaCita)
                                ->where('fecha_hora', '<', $horaFin);
                        });
                })
                ->exists();

            if ($citasSuperpuestas) {
                $horariosDisponibles = $this->getAvailableTimes($fechaCita->format('Y-m-d'), $cita->id);
                return response()->json([
                    'success' => false,
                    'message' => 'El horario seleccionado está ocupado.',
                    'data' => [
                        'available_times' => $horariosDisponibles,
                        'duracion_total' => $duracionTotal
                    ]
                ], 409);
            }

            // Actualizar cita
            $cita->update([
                'vehiculo_id' => $validated['vehiculo_id'],
                'fecha_hora' => $fechaCita,
                'estado' => $nuevoEstado,
                'observaciones' => $validated['observaciones'] ?? null
            ]);

            // Sincronizar servicios
            $serviciosConPrecio = [];
            foreach ($validated['servicios'] as $servicioId) {
                $servicio = Servicio::find($servicioId);
                $serviciosConPrecio[$servicioId] = ['precio' => $servicio->precio];
            }
            $cita->servicios()->sync($serviciosConPrecio);

            DB::commit();

            // Recargar la cita con relaciones
            $cita->load(['vehiculo', 'servicios']);

            return response()->json([
                'success' => true,
                'message' => 'Cita actualizada exitosamente',
                'data' => [
                    'cita_id' => $cita->id,
                    'fecha_hora' => $fechaCita->format('Y-m-d H:i:s'),
                    'servicios_nombres' => $cita->servicios->pluck('nombre')->join(', '),
                    'vehiculo_marca' => $cita->vehiculo->marca,
                    'vehiculo_modelo' => $cita->vehiculo->modelo,
                    'vehiculo_placa' => $cita->vehiculo->placa ?? '',
                    'nuevo_estado' => $nuevoEstado
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar cita: ' . $e->getMessage(), [
                'cita_id' => $cita->id,
                'request' => $request->all(),
                'error' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getHorariosOcupados(Request $request)
    {
        try {
            $fecha = $request->query('fecha');
            $excludeCitaId = $request->query('exclude');

            if (!$fecha) {
                return response()->json(['horariosOcupados' => []]);
            }

            // Validar formato de fecha y crear Carbon instance sin problemas de timezone
            try {
                $fechaCarbon = \Carbon\Carbon::createFromFormat('Y-m-d', $fecha)->startOfDay();
                Log::info("Fecha procesada correctamente:", [
                    'fecha_input' => $fecha,
                    'fecha_carbon' => $fechaCarbon->toDateString(),
                    'dia_semana' => $fechaCarbon->dayOfWeek,
                    'dia_semana_iso' => $fechaCarbon->dayOfWeekIso,
                    'nombre_dia' => $fechaCarbon->locale('es')->dayName
                ]);
            } catch (\Exception $e) {
                Log::error("Error al parsear fecha:", ['fecha' => $fecha, 'error' => $e->getMessage()]);
                return response()->json(['horariosOcupados' => []], 400);
            }

            $query = Cita::with('servicios')
                ->whereDate('fecha_hora', $fechaCarbon)
                ->where('estado', '!=', 'cancelada');

            // Excluir cita específica si se proporciona
            if ($excludeCitaId) {
                $query->where('id', '!=', $excludeCitaId);
                Log::info("Excluyendo cita ID: {$excludeCitaId} para fecha: {$fecha}");
            }

            $citas = $query->get();

            $horariosOcupados = $citas->map(function ($cita) {
                $horaInicio = \Carbon\Carbon::parse($cita->fecha_hora);
                $duracionTotal = $cita->servicios->sum('duracion_min') ?: 30; // Default 30 min

                return [
                    'cita_id' => $cita->id, // Para debug
                    'hora_inicio' => $horaInicio->format('H:i'),
                    'duracion' => $duracionTotal,
                    'hora_fin' => $horaInicio->copy()->addMinutes($duracionTotal)->format('H:i')
                ];
            });

            Log::info("Horarios ocupados para {$fecha}:", [
                'exclude_cita_id' => $excludeCitaId,
                'total_citas' => $citas->count(),
                'horarios_ocupados' => $horariosOcupados->toArray()
            ]);

            return response()->json(['horariosOcupados' => $horariosOcupados]);
        } catch (\Exception $e) {
            Log::error('Error en getHorariosOcupados: ' . $e->getMessage(), [
                'fecha' => $request->query('fecha'),
                'exclude' => $request->query('exclude'),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['horariosOcupados' => []], 500);
        }
    }

    public function historial()
    {
        $user = auth()->user();

        // Actualizar citas expiradas
        $user->citas()
            ->expiradas()
            ->each(function ($cita) {
                $cita->marcarComoExpirada();
            });

        // Obtener citas para historial
        $citas = $user->citas()
            ->with(['servicios', 'vehiculo'])
            ->whereIn('estado', [Cita::ESTADO_FINALIZADA, Cita::ESTADO_CANCELADA])
            ->orderBy('fecha_hora', 'desc');


        if (request()->has('estado') && request('estado') != '') {
            $citas->where('estado', request('estado'));
        }

        if (request()->has('fecha_desde') && request('fecha_desde') != '') {
            $citas->whereDate('fecha_hora', '>=', request('fecha_desde'));
        }

        if (request()->has('fecha_hasta') && request('fecha_hasta') != '') {
            $citas->whereDate('fecha_hora', '<=', request('fecha_hasta'));
        }

        if (request()->has('vehiculo_id') && request('vehiculo_id') != '') {
            $citas->where('vehiculo_id', request('vehiculo_id'));
        }

        // Paginar resultados
        $citas = $citas->paginate(15)->withQueryString();

        return view('cliente.historial', [
            'citas' => $citas,
            'user' => $user
        ]);
    }


    public function debugFechas(Request $request)
    {
        $fecha = $request->query('fecha', now()->format('Y-m-d'));

        try {
            // Crear fecha con Carbon en timezone local
            $fechaCarbon = \Carbon\Carbon::createFromFormat('Y-m-d', $fecha, config('app.timezone', 'America/El_Salvador'))->startOfDay();

            // Información de debug
            $debug = [
                'fecha_original' => $fecha,
                'fecha_carbon' => $fechaCarbon->toDateString(),
                'fecha_carbon_formatted' => $fechaCarbon->format('Y-m-d H:i:s'), // SIN timezone para frontend
                'dia_semana_js' => $fechaCarbon->dayOfWeek, // 0=Domingo, 1=Lunes... 6=Sábado
                'dia_semana_iso' => $fechaCarbon->dayOfWeekIso, // 1=Lunes, 2=Martes... 7=Domingo
                'nombre_dia' => $fechaCarbon->locale('es')->dayName,
                'es_domingo_js' => $fechaCarbon->dayOfWeek === 0,
                'es_domingo_iso' => $fechaCarbon->dayOfWeekIso === 7,
                'timezone' => $fechaCarbon->timezone->getName()
            ];

            // Obtener horarios programados
            $horariosDisponibles = \App\Models\Horario::where('activo', true)->get();

            $debug['horarios_bd'] = $horariosDisponibles->map(function ($horario) {
                return [
                    'id' => $horario->id,
                    'dia_semana' => $horario->dia_semana,
                    'nombre_dia' => $this->getNombreDiaISO($horario->dia_semana),
                    'hora_inicio' => $horario->hora_inicio->format('H:i'),
                    'hora_fin' => $horario->hora_fin->format('H:i'),
                    'activo' => $horario->activo
                ];
            });

            // Verificar qué horarios coinciden con la fecha seleccionada
            $horariosCoincidentes = $horariosDisponibles->where('dia_semana', $fechaCarbon->dayOfWeekIso);

            $debug['horarios_coincidentes'] = $horariosCoincidentes->map(function ($horario) {
                return [
                    'id' => $horario->id,
                    'dia_semana' => $horario->dia_semana,
                    'hora_inicio' => $horario->hora_inicio->format('H:i'),
                    'hora_fin' => $horario->hora_fin->format('H:i')
                ];
            });

            return response()->json($debug);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    private function getNombreDiaISO($diaISO)
    {
        $dias = [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo'
        ];

        return $dias[$diaISO] ?? 'Desconocido';
    }
    /**
     * Método para debugging - Mostrar citas de cualquier usuario en JSON
     */
    public function debugCitasUsuarioJson($usuarioId)
    {
        // Validar que el usuario que hace la solicitud sea admin
        if (!Auth::user() || !Auth::user()->isAdmin()) {
            return response()->json([
                'error' => true,
                'message' => 'Acceso no autorizado'
            ], 403);
        }

        try {
            // Obtener el usuario
            $usuario = Usuario::findOrFail($usuarioId);

            // Obtener todas las citas del usuario con relaciones
            $citas = Cita::where('usuario_id', $usuarioId)
                ->with(['vehiculo', 'servicios', 'usuario'])
                ->orderBy('fecha_hora', 'desc')
                ->get();

            // Formatear los datos para la respuesta JSON
            $response = [
                'usuario' => [
                    'id' => $usuario->id,
                    'nombre' => $usuario->nombre,
                    'email' => $usuario->email,
                    'estado' => $usuario->estado,
                ],
                'estadisticas' => [
                    'total_citas' => $citas->count(),
                    'pendientes' => $citas->where('estado', 'pendiente')->count(),
                    'confirmadas' => $citas->where('estado', 'confirmada')->count(),
                    'en_proceso' => $citas->where('estado', 'en_proceso')->count(),
                    'finalizadas' => $citas->where('estado', 'finalizada')->count(),
                    'canceladas' => $citas->where('estado', 'cancelada')->count(),
                ],
                'citas' => $citas->map(function ($cita) {
                    return [
                        'id' => $cita->id,
                        'fecha_hora' => $cita->fecha_hora->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s'),
                        'fecha_hora_formateada' => $cita->fecha_hora->setTimezone(config('app.timezone'))->isoFormat('dddd D [de] MMMM [de] YYYY, h:mm A'),
                        'estado' => $cita->estado,
                        'vehiculo' => [
                            'id' => $cita->vehiculo->id,
                            'marca' => $cita->vehiculo->marca,
                            'modelo' => $cita->vehiculo->modelo,
                            'placa' => $cita->vehiculo->placa,
                            'tipo' => $cita->vehiculo->tipo,
                        ],
                        'servicios' => $cita->servicios->map(function ($servicio) {
                            return [
                                'id' => $servicio->id,
                                'nombre' => $servicio->nombre,
                                'precio' => $servicio->precio,
                                'duracion_min' => $servicio->duracion_min,
                            ];
                        }),
                        'duracion_total' => $cita->servicios->sum('duracion_min'),
                        'precio_total' => $cita->servicios->sum('precio'),
                        'observaciones' => $cita->observaciones,
                        'created_at' => $cita->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $cita->updated_at->format('Y-m-d H:i:s'),
                    ];
                }),
                'meta' => [
                    'fecha_consulta' => now()->toDateTimeString(),
                    'total_registros' => $citas->count(),
                ]
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}
