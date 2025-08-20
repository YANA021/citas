<?php

namespace App\Http\Middleware;

use App\Models\Bitacora;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BitacoraMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (class_exists(Bitacora::class)) {
            $accion = $request->method();
            Bitacora::registrar($accion, Auth::id(), $request->ip());
        }

        return $response;
    }
}