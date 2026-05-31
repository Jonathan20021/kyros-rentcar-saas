<?php
namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Models\ActivityLog;
use App\Models\StorageRequest;
use App\Services\StorageService;

class StorageController extends AdminController
{
    /** Tenant-facing storage overview + request flow. */
    public function index(Request $request): void
    {
        $tid = $this->tenantId();
        $this->renderAdmin('admin/storage/index', [
            'title'       => 'Almacenamiento',
            'active'      => 'storage',
            'snapshot'    => StorageService::snapshot($tid),
            'breakdown'   => StorageService::usageBreakdown($tid),
            'pending'     => StorageRequest::pendingForTenant($tid),
            'history'     => StorageRequest::historyForTenant($tid, 10),
        ]);
    }

    /** Force a fresh recompute (useful after bulk deletes). */
    public function refresh(Request $request): void
    {
        $tid = $this->tenantId();
        StorageService::recompute($tid);
        Session::flash('success', 'Almacenamiento recalculado.');
        $this->redirect('/admin/storage');
    }

    /** Submit a new "request more space" ticket. */
    public function request(Request $request): void
    {
        $tid = $this->tenantId();
        $mb = max(0, (int) $request->input('extra_mb', 0));
        $reason = trim((string) $request->input('reason', ''));
        if ($mb < 100 || $mb > 100000) {
            Session::flash('error', 'Solicita entre 100 MB y 100 GB.');
            $this->redirect('/admin/storage');
        }
        $auth = \App\Core\Auth::user();
        $id = StorageRequest::submit($tid, $mb, $reason !== '' ? $reason : null, (int) ($auth['id'] ?? 0));
        if ($id) {
            ActivityLog::record('requested', 'storage', $id, 'Solicitud de ' . $mb . ' MB extra');
            Session::flash('success', 'Solicitud enviada. Un administrador la revisará pronto.');
        } else {
            Session::flash('error', 'No se pudo enviar la solicitud.');
        }
        $this->redirect('/admin/storage');
    }

    /** Cancel a pending request. */
    public function cancel(Request $request, string $id): void
    {
        $tid = $this->tenantId();
        if (StorageRequest::cancel((int) $id, $tid)) {
            ActivityLog::record('cancelled', 'storage', (int) $id, 'Solicitud de almacenamiento cancelada');
            Session::flash('success', 'Solicitud cancelada.');
        }
        $this->redirect('/admin/storage');
    }
}
