<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    // Habilita $this->authorize(...) en los controladores (Policies en app/Policies).
    use AuthorizesRequests;
}
