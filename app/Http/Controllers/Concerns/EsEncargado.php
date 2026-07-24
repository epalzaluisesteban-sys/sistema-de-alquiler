<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Auth;

trait EsEncargado
{
    private function esEncargado(): bool
    {
        return strtolower(trim(Auth::user()->role ?? '')) === 'encargado';
    }
}
