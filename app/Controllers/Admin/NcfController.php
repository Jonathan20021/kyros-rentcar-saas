<?php
namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Models\ActivityLog;
use App\Models\NcfSequence;
use App\Models\Tenant;
use App\Services\LocaleService;

class NcfController extends AdminController
{
    public function index(Request $request): void
    {
        $tid = $this->tenantId();
        $tenant = Tenant::find($tid, null);
        $country = strtoupper($tenant['country'] ?? 'DO');

        $this->renderAdmin('admin/ncf/index', [
            'title'       => 'Comprobantes fiscales',
            'active'      => 'ncf',
            'country'     => $country,
            'tenant'      => $tenant,
            'locale'      => LocaleService::forTenant($tenant),
            'sequences'   => NcfSequence::forTenant($tid),
            'types'       => NcfSequence::TYPES,
            'breadcrumbs' => [['label'=>'Dashboard','url'=>url('/admin/dashboard')],['label'=>'Comprobantes']],
        ]);
    }

    public function store(Request $request): void
    {
        $tid = $this->tenantId();
        $type   = strtoupper((string) $request->input('ncf_type'));
        $start  = (int) $request->input('start', 1);
        $max    = (int) $request->input('max', 0);
        $valid  = $request->str('valid_until') ?: null;
        $notes  = $request->str('notes') ?: null;

        $id = NcfSequence::add($tid, $type, $start, $max, $valid, $notes);
        if ($id) {
            ActivityLog::record('created', 'ncf_sequences', $id, "NCF $type · {$start}-{$max}");
            Session::flash('success', 'Secuencia NCF registrada.');
        } else {
            Session::flash('error', 'No se pudo registrar la secuencia. Verifica los rangos y el tipo.');
        }
        $this->redirect('/admin/ncf');
    }

    public function disable(Request $request, string $id): void
    {
        $tid = $this->tenantId();
        if (NcfSequence::disable($tid, (int)$id)) {
            ActivityLog::record('disabled', 'ncf_sequences', (int)$id, 'Secuencia NCF deshabilitada');
            Session::flash('success', 'Secuencia deshabilitada.');
        }
        $this->redirect('/admin/ncf');
    }
}
