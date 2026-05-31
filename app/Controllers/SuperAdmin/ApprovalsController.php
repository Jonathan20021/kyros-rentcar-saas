<?php
namespace App\Controllers\SuperAdmin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Models\StorageRequest;
use App\Services\StorageService;

/**
 * Single hub for super-admin approvals:
 *   - Pending tenant activations (fresh registrations)
 *   - Pending storage-extra requests
 *
 * Approve/reject actions log to `tenant_approvals` for audit.
 */
class ApprovalsController extends Controller
{
    public function index(Request $request): void
    {
        // Queue: pending tenants
        $pendingTenants = Database::select(
            "SELECT t.id, t.name, t.slug, t.email, t.phone, t.address, t.created_at,
                    p.name AS plan_name, p.slug AS plan_slug, p.storage_mb,
                    (SELECT COUNT(*) FROM users u WHERE u.tenant_id = t.id) AS users_count
               FROM tenants t
          LEFT JOIN plans p ON p.id = t.plan_id
              WHERE t.status = 'pending_approval' AND t.deleted_at IS NULL
           ORDER BY t.created_at ASC"
        );
        // Queue: pending storage requests (with tenant + usage snapshot)
        $rawRequests = StorageRequest::queue();
        $requests = [];
        foreach ($rawRequests as $r) {
            $r['snapshot'] = StorageService::snapshot((int) $r['tenant_id']);
            $requests[] = $r;
        }
        // Stats
        $stats = [
            'pending_tenants'  => count($pendingTenants),
            'pending_storage'  => count($requests),
            'approved_today'   => (int) Database::scalar(
                "SELECT COUNT(*) FROM tenant_approvals WHERE action='approved' AND DATE(created_at) = CURDATE()"
            ),
        ];

        $this->view('superadmin/approvals/index', [
            'title'           => 'Aprobaciones · Super Admin',
            'active'          => 'approvals',
            'pendingTenants'  => $pendingTenants,
            'requests'        => $requests,
            'stats'           => $stats,
        ]);
    }

    /** Approve a pending tenant — status → active and log. */
    public function approveTenant(Request $request, string $id): void
    {
        $id = (int) $id;
        $reviewer = (int) (Auth::user()['id'] ?? 0);
        $note = trim((string) $request->input('note', '')) ?: null;
        Database::beginTransaction();
        try {
            Database::execute(
                "UPDATE tenants SET status = 'active' WHERE id = :id AND status = 'pending_approval'",
                ['id' => $id]
            );
            Database::execute(
                "INSERT INTO tenant_approvals (tenant_id, action, performed_by, note)
                 VALUES (:t, 'approved', :u, :n)",
                ['t' => $id, 'u' => $reviewer, 'n' => $note]
            );
            Database::commit();
            Session::flash('success', 'Empresa activada.');
        } catch (\Throwable $e) {
            Database::rollBack();
            Session::flash('error', 'No se pudo activar la empresa.');
        }
        $this->redirect('/super-admin/approvals');
    }

    /** Reject a tenant signup — soft-delete and log. */
    public function rejectTenant(Request $request, string $id): void
    {
        $id = (int) $id;
        $reviewer = (int) (Auth::user()['id'] ?? 0);
        $note = trim((string) $request->input('note', '')) ?: 'Rechazado por super admin';
        Database::beginTransaction();
        try {
            Database::execute(
                "UPDATE tenants SET status = 'inactive', deleted_at = NOW() WHERE id = :id AND status = 'pending_approval'",
                ['id' => $id]
            );
            Database::execute(
                "INSERT INTO tenant_approvals (tenant_id, action, performed_by, note)
                 VALUES (:t, 'rejected', :u, :n)",
                ['t' => $id, 'u' => $reviewer, 'n' => $note]
            );
            Database::commit();
            Session::flash('success', 'Solicitud rechazada.');
        } catch (\Throwable $e) {
            Database::rollBack();
            Session::flash('error', 'No se pudo procesar el rechazo.');
        }
        $this->redirect('/super-admin/approvals');
    }

    /** Approve a storage request — adds the granted MB to the tenant. */
    public function approveStorage(Request $request, string $id): void
    {
        $reviewer = (int) (Auth::user()['id'] ?? 0);
        $grant = (int) $request->input('granted_mb', 0);
        $note  = trim((string) $request->input('note', '')) ?: null;
        // Default to the requested amount if super admin didn't override.
        if ($grant <= 0) {
            $req = Database::selectOne("SELECT requested_mb FROM storage_requests WHERE id = :id", ['id' => (int)$id]);
            $grant = (int) ($req['requested_mb'] ?? 0);
        }
        if (StorageRequest::approve((int)$id, $reviewer, $grant, $note)) {
            Session::flash('success', '+' . number_format($grant) . ' MB añadidos al tenant.');
        } else {
            Session::flash('error', 'No se pudo aprobar la solicitud.');
        }
        $this->redirect('/super-admin/approvals');
    }

    /** Reject a storage request. */
    public function rejectStorage(Request $request, string $id): void
    {
        $reviewer = (int) (Auth::user()['id'] ?? 0);
        $note = trim((string) $request->input('note', '')) ?: null;
        if (StorageRequest::reject((int)$id, $reviewer, $note)) {
            Session::flash('success', 'Solicitud rechazada.');
        } else {
            Session::flash('error', 'No se pudo rechazar.');
        }
        $this->redirect('/super-admin/approvals');
    }
}
