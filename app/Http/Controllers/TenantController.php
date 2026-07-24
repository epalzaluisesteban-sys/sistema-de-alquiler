<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class TenantController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();

        return view('Inquilino.dashboard', compact('usuario'));
    }

    public function residencias()
    {
        $usuario = Auth::user();

        return view('Inquilino.dashboard', compact('usuario'));
    }
}
