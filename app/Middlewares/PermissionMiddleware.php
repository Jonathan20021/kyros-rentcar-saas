<?php
namespace App\Middlewares;

use App\Core\Auth;
use App\Core\Request;
use App\Core\View;

/**
 * Checks a single permission slug. Instantiate with the required permission:
 *   new PermissionMiddleware('vehicles.create')
 */
class PermissionMiddleware
{
    protected string $permission;

    public function __construct(string $permission)
    {
        $this->permission = $permission;
    }

    public function handle(Request $request): void
    {
        if (!Auth::can($this->permission)) {
            http_response_code(403);
            View::display('errors/403', ['message' => 'No tienes permiso para esta accion.']);
            exit;
        }
    }
}
