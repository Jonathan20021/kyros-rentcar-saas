<?php
namespace App\Middlewares;

use App\Core\Auth;
use App\Core\Request;
use App\Core\View;

/**
 * Ensures the user belongs to a tenant (rent car staff/owner) and is NOT a
 * super admin trying to use tenant routes. Tenant scoping itself is enforced
 * at the model layer via Auth::tenantId().
 */
class TenantMiddleware
{
    public function handle(Request $request): void
    {
        if (Auth::isSuperAdmin()) {
            // Super admin uses the /super-admin panel, not tenant routes.
            header('Location: ' . url('/super-admin'));
            exit;
        }
        if (Auth::tenantId() === null) {
            http_response_code(403);
            View::display('errors/403', ['message' => 'No perteneces a ninguna empresa.']);
            exit;
        }
    }
}
