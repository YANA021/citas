<?php
// app/Http/Controllers/PerfilController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Bitacora;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PerfilController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('perfil.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'nombre' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
        ]);

        $user->update([
            'nombre' => $request->nombre,
            'telefono' => $request->telefono,
        ]);
        Bitacora::registrar(Bitacora::ACCION_ACTUALIZAR_PERFIL, null, $request->ip());

        return back()->with('success', 'Perfil actualizado correctamente');
    }

    public function configuracion()
    {
        return view('configuracion', ['user' => Auth::user()]);
    }

    public function updateEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255|unique:usuarios,email,' . Auth::id(),
        ]);

        Auth::user()->update(['email' => $request->email]);
        Bitacora::registrar(Bitacora::ACCION_ACTUALIZAR_EMAIL, null, $request->ip());

        return back()->with('success', 'Email actualizado correctamente');
    }

    public function updatePassword(Request $request)
{
    $request->validate([
        'current_password' => 'required|string|current_password',
        'password' => 'required|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
    ], [
        'password.regex' => 'La contraseña debe contener al menos una mayúscula, una minúscula y un número.',
        'password.confirmed' => 'Las contraseñas no coinciden.'
    ]);

    Auth::user()->update(['password' => Hash::make($request->password)]);
    Bitacora::registrar(Bitacora::ACCION_ACTUALIZAR_PASSWORD, null, $request->ip());

    return back()->with('success', 'Contraseña actualizada correctamente');
}
    public function updateAjax(Request $request)
{
    $validated = $request->validate([
        'nombre' => 'required|string|max:255',
       'telefono' => 'nullable|string|digits:8|unique:usuarios,telefono,'.Auth::id()
    ]);

    try {
        $user = Auth::user();
        $user->update($validated);
        Bitacora::registrar(Bitacora::ACCION_ACTUALIZAR_PERFIL, null, $request->ip());

        return response()->json([
            'success' => true,
            'message' => 'Perfil actualizado correctamente',
            'user' => $user->fresh() 
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error actualizando perfil: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error al actualizar el perfil. Intente nuevamente.'
        ], 500);
    }
}
}
