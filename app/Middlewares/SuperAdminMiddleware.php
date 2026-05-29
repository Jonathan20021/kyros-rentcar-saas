<?php
namespace App\Middlewares;

use App\Core\Auth;
use App\Core\Request;
use App\Core\View;

/** Restricts access to the Kyros Super Admin panel. */
class SuperAdminMiddleware
{
    public function handle(Request $request): void
    {
        if (!Auth::isSuperAdmin()) {
            http_response_code(403);
            View::display('errors/403', ['message' => 'Acceso restringido al Super Admin de Kyros.']);
            exit;
        }
    }
}
