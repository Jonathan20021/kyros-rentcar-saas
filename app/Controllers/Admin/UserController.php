<?php
namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Core\Database;
use App\Core\Auth;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Location;
use App\Models\ActivityLog;

class UserController extends AdminController
{
    /** Roles assignable to tenant staff (excludes system + customer). */
    protected function roles(): array
    {
        return Database::select("SELECT id, name, slug FROM roles WHERE scope='tenant' AND slug <> 'customer' ORDER BY id");
    }

    public function index(Request $request): void
    {
        $tid = $this->tenantId();
        $tenant = Tenant::withPlan($tid);
        $this->renderAdmin('admin/users/index', [
            'title'   => 'Equipo · Kyros',
            'active'  => 'users',
            'users'   => User::forTenant($tid),
            'count'   => User::countForTenant($tid),
            'maxUsers'=> (int) ($tenant['max_users'] ?? -1),
            'breadcrumbs' => [['label'=>'Dashboard','url'=>url('/admin/dashboard')],['label'=>'Equipo']],
        ]);
    }

    public function create(Request $request): void
    {
        $tid = $this->tenantId();
        if (!\App\Models\Plan::canAdd($tid, 'users')) {
            Session::flash('error', 'Tu plan alcanzó el límite de usuarios. Actualiza para invitar más miembros.');
            $this->redirect('/admin/users');
        }
        $this->renderAdmin('admin/users/form', [
            'title'  => 'Invitar usuario · Kyros',
            'active' => 'users',
            'user'   => null,
            'roles'  => $this->roles(),
            'locations' => Location::activeForTenant($tid),
            'breadcrumbs' => [['label'=>'Equipo','url'=>url('/admin/users')],['label'=>'Nuevo']],
        ]);
    }

    public function store(Request $request): void
    {
        $tid = $this->tenantId();
        $back = '/admin/users/create';
        $data = $this->validateOrBack($request->all(), [
            'name'     => 'required|min:3|max:120',
            'email'    => 'required|email|max:150',
            'role_id'  => 'required|integer',
            'password' => 'required|min:8',
        ], $back);

        // Plan seat limit
        $tenant = Tenant::withPlan($tid);
        $max = (int) ($tenant['max_users'] ?? -1);
        if ($max >= 0 && User::countForTenant($tid) >= $max) {
            Session::flash('error', 'Alcanzaste el límite de usuarios de tu plan ('.$max.'). Actualiza tu plan para agregar más.');
            $this->redirect('/admin/users');
        }

        if (!$this->roleAllowed((int) $data['role_id'])) {
            Session::flash('error', 'Rol inválido.'); $this->redirect($back);
        }
        $email = strtolower($data['email']);
        if (User::emailExists($email, $tid) || Database::scalar("SELECT COUNT(*) FROM users WHERE email=:e", ['e'=>$email])) {
            Session::flash('error', 'Ese correo ya está en uso.');
            Session::flashInput($request->all());
            $this->redirect($back);
        }

        $id = User::create([
            'tenant_id' => $tid,
            'role_id'   => (int) $data['role_id'],
            'name'      => $data['name'],
            'email'     => $email,
            'password'  => password_hash($data['password'], PASSWORD_BCRYPT),
            'phone'     => $request->str('phone') ?: null,
            'location_id' => $request->int('location_id') ?: null,
            'status'    => 'active',
            'email_verified_at' => date('Y-m-d H:i:s'),
        ]);
        // Invitation email with credentials (Resend; falls back to log)
        try {
            $tname = $tenant['name'] ?? 'la empresa';
            $body = '<p>Hola <strong>'.e($data['name']).'</strong>,</p>'
                . '<p>Fuiste agregado al equipo de <strong>'.e($tname).'</strong> en Kyros Rent Car.</p>'
                . '<p>Tus credenciales de acceso:</p>'
                . '<table style="margin:10px 0;font-size:14px"><tr><td style="color:#6b7280;padding:2px 12px 2px 0">Correo</td><td style="font-weight:600">'.e($email).'</td></tr>'
                . '<tr><td style="color:#6b7280;padding:2px 12px 2px 0">Contraseña</td><td style="font-weight:600">'.e($data['password']).'</td></tr></table>'
                . '<p style="color:#6b7280;font-size:13px">Te recomendamos cambiarla luego de iniciar sesión.</p>';
            \App\Services\Mailer::send($email, 'Te invitaron a Kyros Rent Car',
                \App\Services\Mailer::layout('Bienvenido al equipo', $body, $tenant, ['label'=>'Iniciar sesión','url'=>abs_url('/login')]));
        } catch (\Throwable $e) { \App\Core\Logger::error('invite mail: '.$e->getMessage()); }

        ActivityLog::record('created','users',$id,'Usuario del equipo: '.$data['name']);
        Session::flash('success','Usuario agregado al equipo.');
        $this->redirect('/admin/users');
    }

    public function edit(Request $request, string $id): void
    {
        $user = $this->findTenantUser((int) $id);
        $this->renderAdmin('admin/users/form', [
            'title'  => 'Editar usuario · Kyros',
            'active' => 'users',
            'user'   => $user,
            'roles'  => $this->roles(),
            'locations' => Location::activeForTenant($this->tenantId()),
            'breadcrumbs' => [['label'=>'Equipo','url'=>url('/admin/users')],['label'=>'Editar']],
        ]);
    }

    public function update(Request $request, string $id): void
    {
        $tid = $this->tenantId();
        $user = $this->findTenantUser((int) $id);
        $data = $this->validateOrBack($request->all(), [
            'name'    => 'required|min:3|max:120',
            'role_id' => 'required|integer',
            'status'  => 'required|in:active,inactive,blocked',
        ], '/admin/users/edit/'.$id);

        if (!$this->roleAllowed((int) $data['role_id'])) { Session::flash('error','Rol inválido.'); $this->redirect('/admin/users/edit/'.$id); }

        $payload = ['name'=>$data['name'],'role_id'=>(int)$data['role_id'],'status'=>$data['status'],'phone'=>$request->str('phone') ?: null,'location_id'=>$request->int('location_id') ?: null];
        if ($request->str('password')) {
            if (mb_strlen($request->str('password')) < 8) { Session::flash('error','La contraseña debe tener al menos 8 caracteres.'); $this->redirect('/admin/users/edit/'.$id); }
            $payload['password'] = password_hash($request->str('password'), PASSWORD_BCRYPT);
        }
        // Don't let a user lock themselves out.
        if ((int)$id === Auth::id() && $data['status'] !== 'active') {
            Session::flash('error','No puedes desactivar tu propia cuenta.'); $this->redirect('/admin/users/edit/'.$id);
        }

        Database::execute(
            "UPDATE users SET name=:n, role_id=:r, status=:s, phone=:p, location_id=:loc".(isset($payload['password'])?', password=:pw':'')." WHERE id=:id AND tenant_id=:t",
            array_merge(['n'=>$payload['name'],'r'=>$payload['role_id'],'s'=>$payload['status'],'p'=>$payload['phone'],'loc'=>$payload['location_id'],'id'=>(int)$id,'t'=>$tid], isset($payload['password'])?['pw'=>$payload['password']]:[])
        );
        ActivityLog::record('updated','users',(int)$id,'Usuario actualizado');
        Session::flash('success','Usuario actualizado.');
        $this->redirect('/admin/users');
    }

    public function toggle(Request $request, string $id): void
    {
        $tid = $this->tenantId();
        $user = $this->findTenantUser((int) $id);
        if ((int)$id === Auth::id()) { Session::flash('error','No puedes desactivar tu propia cuenta.'); $this->redirect('/admin/users'); }
        $new = $user['status'] === 'active' ? 'inactive' : 'active';
        Database::execute("UPDATE users SET status=:s WHERE id=:id AND tenant_id=:t", ['s'=>$new,'id'=>(int)$id,'t'=>$tid]);
        ActivityLog::record('change_status','users',(int)$id,'Usuario '.$new);
        Session::flash('success', $new==='active' ? 'Usuario activado.' : 'Usuario desactivado.');
        $this->redirect('/admin/users');
    }

    protected function roleAllowed(int $roleId): bool
    {
        foreach ($this->roles() as $r) { if ((int)$r['id'] === $roleId) return true; }
        return false;
    }

    protected function findTenantUser(int $id): array
    {
        $tid = $this->tenantId();
        $u = Database::selectOne("SELECT * FROM users WHERE id=:id AND tenant_id=:t AND deleted_at IS NULL", ['id'=>$id,'t'=>$tid]);
        if (!$u) { $this->abort(404, 'Usuario no encontrado'); }
        return $u;
    }
}
