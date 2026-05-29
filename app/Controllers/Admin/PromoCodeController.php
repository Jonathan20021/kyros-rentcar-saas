<?php
namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Models\PromoCode;
use App\Models\ActivityLog;

class PromoCodeController extends AdminController
{
    public function index(Request $request): void
    {
        $tid = $this->tenantId();
        $filters = [
            'status' => $request->str('status'),
            'search' => $request->str('search'),
        ];
        $this->renderAdmin('admin/promos/index', [
            'title'   => 'Códigos promocionales · Kyros',
            'active'  => 'promos',
            'promos'  => PromoCode::listForTenant($tid, $filters),
            'filters' => $filters,
            'breadcrumbs' => [['label'=>'Dashboard','url'=>url('/admin/dashboard')],['label'=>'Promociones']],
        ]);
    }

    public function create(Request $request): void
    {
        $this->renderAdmin('admin/promos/form', [
            'title'  => 'Nuevo código · Kyros',
            'active' => 'promos',
            'promo'  => null,
            'breadcrumbs' => [['label'=>'Promociones','url'=>url('/admin/promos')],['label'=>'Nuevo']],
        ]);
    }

    public function edit(Request $request, string $id): void
    {
        $tid = $this->tenantId();
        $promo = PromoCode::findOrFail((int) $id, $tid);
        $this->renderAdmin('admin/promos/form', [
            'title'  => 'Editar código · Kyros',
            'active' => 'promos',
            'promo'  => $promo,
            'breadcrumbs' => [['label'=>'Promociones','url'=>url('/admin/promos')],['label'=>$promo['code']]],
        ]);
    }

    public function store(Request $request): void
    {
        if (!can('promos.manage')) { $this->abort(403); }
        $tid = $this->tenantId();
        $data = $this->validated($request, '/admin/promos/create');
        $data['tenant_id'] = $tid;
        $id = PromoCode::create($data);
        ActivityLog::record('created', 'promo_codes', $id, 'Código creado: ' . $data['code']);
        Session::flash('success', 'Código promocional creado.');
        $this->redirect('/admin/promos');
    }

    public function update(Request $request, string $id): void
    {
        if (!can('promos.manage')) { $this->abort(403); }
        $tid = $this->tenantId();
        PromoCode::findOrFail((int) $id, $tid);
        $data = $this->validated($request, '/admin/promos/edit/' . $id);
        unset($data['tenant_id']);
        PromoCode::update((int) $id, $tid, $data);
        ActivityLog::record('updated', 'promo_codes', (int) $id, 'Código actualizado: ' . $data['code']);
        Session::flash('success', 'Código actualizado.');
        $this->redirect('/admin/promos');
    }

    public function destroy(Request $request, string $id): void
    {
        if (!can('promos.manage')) { $this->abort(403); }
        $tid = $this->tenantId();
        PromoCode::findOrFail((int) $id, $tid);
        PromoCode::delete((int) $id, $tid);
        ActivityLog::record('deleted', 'promo_codes', (int) $id, 'Código eliminado');
        Session::flash('success', 'Código eliminado.');
        $this->redirect('/admin/promos');
    }

    protected function validated(Request $request, string $back): array
    {
        $v = $this->validateOrBack($request->all(), [
            'code'           => 'required|max:40',
            'discount_type'  => 'required|in:percent,fixed',
            'discount_value' => 'required|numeric|min:0',
            'status'         => 'required|in:active,inactive',
        ], $back);
        return [
            'code'           => strtoupper(preg_replace('/[^A-Z0-9\-_]/i','', $v['code'])),
            'description'    => $request->str('description') ?: null,
            'discount_type'  => $v['discount_type'],
            'discount_value' => (float) $v['discount_value'],
            'min_amount'     => (float) ($request->str('min_amount') ?: 0),
            'max_uses'       => $request->str('max_uses') !== '' ? (int) $request->str('max_uses') : null,
            'valid_from'     => $request->str('valid_from') ?: null,
            'valid_to'       => $request->str('valid_to') ?: null,
            'is_public'      => $request->str('is_public') === '1' ? 1 : 0,
            'status'         => $v['status'],
        ];
    }
}
