<?php
namespace App\Controllers\SuperAdmin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Core\Database;
use App\Core\Auth;
use App\Models\User;
use App\Models\Tenant;
use App\Models\ActivityLog;

class UserController extends Controller
{
    public function index(Request $request): void
    {
        $this->view('superadmin/users', [
            'title'  => 'Usuarios · Super Admin',
            'panel'  => 'super',
            'active' => 'users',
            'users'  => User::allSystem(),
        ], 'app');
    }

    public function create(Request $request): void
    {
        $this->renderForm(null);
    }

    public function edit(Request $request, string $id): void
    {
        $user = User::findSystem((int) $id);
        if (!$user) { $this->abort(404, 'Usuario no encontrado'); }
        $this->renderForm($user);
    }

    public function store(Request $request): void
    {
        $back = '/super-admin/users/create';
        $data = $this->validateOrBack($request->all(), [
            'name'     => 'required|min:3|max:120',
            'email'    => 'required|email|max:150',
            'password' => 'required|min:8',
        ], $back);

        [$roleId, $tenantId, $err] = $this->resolveRoleAndTenant($request);
        if ($err) { Session::flash('error', $err); Session::flashInput($request->all()); $this->redirect($back); }

        $email = strtolower($data['email']);
        if (User::emailTaken($email)) {
            Session::flash('error', 'Ese correo ya está en uso en la plataforma.');
            Session::flashInput($request->all());
            $this->redirect($back);
        }

        $id = User::create([
            'tenant_id'         => $tenantId,
            'role_id'           => $roleId,
            'name'              => $data['name'],
            'email'             => $email,
            'password'          => password_hash($data['password'], PASSWORD_BCRYPT),
            'phone'             => $request->str('phone') ?: null,
            'status'            => 'active',
            'email_verified_at' => date('Y-m-d H:i:s'),
        ]);

        ActivityLog::record('created', 'users', $id, 'Usuario creado (super admin): ' . $data['name']);
        Session::flash('success', 'Usuario creado correctamente.');
        $this->redirect('/super-admin/users');
    }

    public function update(Request $request, string $id): void
    {
        $uid  = (int) $id;
        $user = User::findSystem($uid);
        if (!$user) { $this->abort(404, 'Usuario no encontrado'); }
        $back = '/super-admin/users/edit/' . $uid;

        $data = $this->validateOrBack($request->all(), [
            'name'   => 'required|min:3|max:120',
            'email'  => 'required|email|max:150',
            'status' => 'required|in:active,inactive,blocked',
        ], $back);

        [$roleId, $tenantId, $err] = $this->resolveRoleAndTenant($request);
        if ($err) { Session::flash('error', $err); $this->redirect($back); }

        $email = strtolower($data['email']);
        if (User::emailTaken($email, $uid)) {
            Session::flash('error', 'Ese correo ya está en uso en la plataforma.');
            $this->redirect($back);
        }

        // Guard: don't lock yourself out, and never leave the platform without an active super admin.
        $isSelf = $uid === Auth::id();
        $losingSuperAdmin = $user['role_slug'] === 'super-admin'
            && ($roleId !== (int) $user['role_id'] || $data['status'] !== 'active');
        if ($losingSuperAdmin && User::activeSuperAdminCount($uid) === 0) {
            Session::flash('error', 'Debe quedar al menos un Super Admin activo.');
            $this->redirect($back);
        }
        if ($isSelf && $data['status'] !== 'active') {
            Session::flash('error', 'No puedes desactivar tu propia cuenta.');
            $this->redirect($back);
        }

        $payload = [
            'name'      => $data['name'],
            'email'     => $email,
            'role_id'   => $roleId,
            'tenant_id' => $tenantId,
            'status'    => $data['status'],
            'phone'     => $request->str('phone') ?: null,
        ];
        if ($request->str('password')) {
            if (mb_strlen($request->str('password')) < 8) {
                Session::flash('error', 'La contraseña debe tener al menos 8 caracteres.');
                $this->redirect($back);
            }
            $payload['password'] = password_hash($request->str('password'), PASSWORD_BCRYPT);
        }

        $sets = [];
        $params = ['id' => $uid];
        foreach ($payload as $col => $val) { $sets[] = "$col = :$col"; $params[$col] = $val; }
        Database::execute("UPDATE users SET " . implode(', ', $sets) . " WHERE id = :id", $params);

        ActivityLog::record('updated', 'users', $uid, 'Usuario actualizado (super admin)');
        Session::flash('success', 'Usuario actualizado.');
        $this->redirect('/super-admin/users');
    }

    public function toggle(Request $request, string $id): void
    {
        $uid  = (int) $id;
        $user = User::findSystem($uid);
        if (!$user) { $this->abort(404, 'Usuario no encontrado'); }

        if ($uid === Auth::id()) {
            Session::flash('error', 'No puedes desactivar tu propia cuenta.');
            $this->redirect('/super-admin/users');
        }
        $new = $user['status'] === 'active' ? 'inactive' : 'active';
        if ($new !== 'active' && $user['role_slug'] === 'super-admin' && User::activeSuperAdminCount($uid) === 0) {
            Session::flash('error', 'Debe quedar al menos un Super Admin activo.');
            $this->redirect('/super-admin/users');
        }
        Database::execute("UPDATE users SET status = :s WHERE id = :id", ['s' => $new, 'id' => $uid]);
        ActivityLog::record('change_status', 'users', $uid, 'Usuario ' . $new . ' (super admin)');
        Session::flash('success', $new === 'active' ? 'Usuario activado.' : 'Usuario desactivado.');
        $this->redirect('/super-admin/users');
    }

    public function destroy(Request $request, string $id): void
    {
        $uid  = (int) $id;
        $user = User::findSystem($uid);
        if (!$user) { $this->abort(404, 'Usuario no encontrado'); }

        if ($uid === Auth::id()) {
            Session::flash('error', 'No puedes eliminar tu propia cuenta.');
            $this->redirect('/super-admin/users');
        }
        if ($user['role_slug'] === 'super-admin' && User::activeSuperAdminCount($uid) === 0) {
            Session::flash('error', 'Debe quedar al menos un Super Admin activo.');
            $this->redirect('/super-admin/users');
        }
        Database::execute("UPDATE users SET deleted_at = NOW() WHERE id = :id", ['id' => $uid]);
        ActivityLog::record('deleted', 'users', $uid, 'Usuario eliminado (super admin): ' . $user['name']);
        Session::flash('success', 'Usuario eliminado.');
        $this->redirect('/super-admin/users');
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    protected function renderForm(?array $user): void
    {
        $this->view('superadmin/users_form', [
            'title'       => $user ? 'Editar usuario · Super Admin' : 'Nuevo usuario · Super Admin',
            'panel'       => 'super',
            'active'      => 'users',
            'user'        => $user,
            'systemRole'  => $this->superAdminRole(),
            'tenantRoles' => $this->tenantRoles(),
            'tenants'     => Tenant::allWithStats(),
        ], 'app');
    }

    /** The single system-scope super-admin role row. */
    protected function superAdminRole(): array
    {
        return Database::selectOne("SELECT id, name, slug FROM roles WHERE slug = 'super-admin' LIMIT 1")
            ?? ['id' => 1, 'name' => 'Super Admin', 'slug' => 'super-admin'];
    }

    /** Roles assignable to tenant users (excludes the storefront customer role). */
    protected function tenantRoles(): array
    {
        return Database::select(
            "SELECT id, name, slug FROM roles WHERE scope = 'tenant' AND slug <> 'customer' ORDER BY id"
        );
    }

    /**
     * Resolve role_id + tenant_id from the submitted account type.
     * Returns [roleId, tenantId, errorOrNull].
     */
    protected function resolveRoleAndTenant(Request $request): array
    {
        $type = $request->str('account_type') === 'tenant' ? 'tenant' : 'system';

        if ($type === 'system') {
            return [(int) $this->superAdminRole()['id'], null, null];
        }

        $roleId   = $request->int('role_id');
        $tenantId = $request->int('tenant_id');

        if (!$tenantId || !Tenant::find($tenantId, null)) {
            return [0, null, 'Selecciona una empresa válida para el usuario.'];
        }
        $allowed = false;
        foreach ($this->tenantRoles() as $r) { if ((int) $r['id'] === $roleId) { $allowed = true; break; } }
        if (!$allowed) {
            return [0, null, 'Selecciona un rol válido para el usuario de empresa.'];
        }
        return [$roleId, $tenantId, null];
    }
}
