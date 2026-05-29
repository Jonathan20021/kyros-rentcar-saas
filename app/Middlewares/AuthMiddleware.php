<?php
namespace App\Middlewares;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Session;

/** Requires an authenticated user. */
class AuthMiddleware
{
    public function handle(Request $request): void
    {
        if (!Auth::check()) {
            Session::flash('error', 'Debes iniciar sesion para continuar.');
            Session::set('_intended', $request->uri());
            header('Location: ' . url('/login'));
            exit;
        }
    }
}
