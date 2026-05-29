<?php
namespace App\Controllers\SuperAdmin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Database;
use App\Core\Session;
use App\Models\Tenant;
use App\Models\Plan;
use App\Models\User;
use App\Models\ActivityLog;

class TenantController extends Controller
{
    public function index(Request $request): void
    {
        $this->view('superadmin/tenants/index', [
            'title'   => 'Empresas · Super Admin',
            'panel'   => 'super',
            'active'  => 'tenants',
            'tenants' => Tenant::allWithStats(),
        ], 'app');
    }

    public function create(Request $request): void
    {
        $this->view('superadmin/tenants/form', [
            'title'  => 'Nueva empresa · Super Admin',
            'panel'  => 'super',
            'active' => 'tenants',
            'tenant' => null,
            'plans'  => Plan::all(null),
        ], 'app');
    }

    public function store(Request $request): void
    {
        $input = $request->only(['name','legal_name','email','phone','plan_id','owner_name','owner_email','owner_password']);
        $data = $this->validateOrBack($input, [
            'name'          => 'required|min:3|max:150',
            'email'         => 'required|email|max:150',
            'plan_id'       => 'required|integer',
            'owner_name'    => 'required|min:3|max:120',
            'owner_email'   => 'required|email|max:150',
            'owner_password'=> 'required|min:8',
        ], '/super-admin/tenants/create');

        if (Database::scalar("SELECT COUNT(*) FROM users WHERE email = :e", ['e' => strtolower($data['owner_email'])])) {
            Session::flash('error', 'El correo del dueno ya esta en uso.');
            Session::flashInput($input);
            $this->redirect('/super-admin/tenants/create');
        }

        try {
            Database::beginTransaction();
            $slug = Tenant::uniqueSlug($data['name']);
            $tenantId = Tenant::create([
                'name'        => $data['name'],
                'slug'        => $slug,
                'legal_name'  => $data['legal_name'] ?? null,
                'email'       => strtolower($data['email']),
                'phone'       => $data['phone'] ?? null,
                'plan_id'     => (int) $data['plan_id'],
                'status'      => 'active',
            ]);
            User::create([
                'tenant_id' => $tenantId,
                'role_id'   => 2,
                'name'      => $data['owner_name'],
                'email'     => strtolower($data['owner_email']),
                'password'  => password_hash($data['owner_password'], PASSWORD_BCRYPT),
                'status'    => 'active',
                'email_verified_at' => date('Y-m-d H:i:s'),
            ]);
            foreach (['Economico','Sedan','SUV','Lujo'] as $cat) {
                Database::execute(
                    "INSERT INTO vehicle_categories (tenant_id, name, slug, status) VALUES (:t,:n,:s,'active')",
                    ['t' => $tenantId, 'n' => $cat, 's' => slugify($cat)]
                );
            }
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            \App\Core\Logger::error('SuperAdmin create tenant failed: ' . $e->getMessage());
            Session::flash('error', 'No se pudo crear la empresa.');
            $this->redirect('/super-admin/tenants/create');
        }

        ActivityLog::record('created', 'tenants', $tenantId, 'Empresa creada: ' . $data['name']);
        Session::flash('success', 'Empresa creada correctamente.');
        $this->redirect('/super-admin/tenants');
    }

    public function edit(Request $request, string $id): void
    {
        $tenant = Tenant::find((int) $id, null);
        if (!$tenant) { $this->abort(404, 'Empresa no encontrada'); }
        $this->view('superadmin/tenants/form', [
            'title'  => 'Editar empresa · Super Admin',
            'panel'  => 'super',
            'active' => 'tenants',
            'tenant' => $tenant,
            'plans'  => Plan::all(null),
        ], 'app');
    }

    public function update(Request $request, string $id): void
    {
        $tenant = Tenant::find((int) $id, null);
        if (!$tenant) { $this->abort(404); }
        $data = $this->validateOrBack($request->only(['name','legal_name','email','phone','plan_id','status']), [
            'name'    => 'required|min:3|max:150',
            'email'   => 'required|email',
            'plan_id' => 'required|integer',
            'status'  => 'required|in:trial,active,suspended,inactive',
        ], '/super-admin/tenants/edit/' . $id);

        Tenant::update((int) $id, null, [
            'name'       => $data['name'],
            'legal_name' => $data['legal_name'] ?? null,
            'email'      => strtolower($data['email']),
            'phone'      => $data['phone'] ?? null,
            'plan_id'    => (int) $data['plan_id'],
            'status'     => $data['status'],
        ]);
        ActivityLog::record('updated', 'tenants', (int) $id, 'Empresa actualizada');
        Session::flash('success', 'Empresa actualizada.');
        $this->redirect('/super-admin/tenants');
    }

    public function suspend(Request $request, string $id): void
    {
        Tenant::update((int) $id, null, ['status' => 'suspended']);
        ActivityLog::record('suspended', 'tenants', (int) $id, 'Empresa suspendida');
        Session::flash('warning', 'Empresa suspendida.');
        $this->redirect('/super-admin/tenants');
    }

    public function activate(Request $request, string $id): void
    {
        Tenant::update((int) $id, null, ['status' => 'active']);
        ActivityLog::record('activated', 'tenants', (int) $id, 'Empresa activada');
        Session::flash('success', 'Empresa activada.');
        $this->redirect('/super-admin/tenants');
    }
}
