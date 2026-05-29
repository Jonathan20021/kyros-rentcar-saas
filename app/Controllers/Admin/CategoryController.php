<?php
namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Models\VehicleCategory;
use App\Models\ActivityLog;

class CategoryController extends AdminController
{
    public function index(Request $request): void
    {
        $tid = $this->tenantId();
        $editId = $request->int('edit');
        $this->renderAdmin('admin/categories/index', [
            'title'      => 'Categorías · Kyros',
            'active'     => 'categories',
            'categories' => VehicleCategory::listWithCounts($tid),
            'editing'    => $editId ? VehicleCategory::find($editId, $tid) : null,
            'breadcrumbs'=> [['label'=>'Dashboard','url'=>url('/admin/dashboard')],['label'=>'Categorías']],
        ]);
    }

    public function store(Request $request): void
    {
        if (!can('catalog.manage')) { $this->abort(403); }
        $tid = $this->tenantId();
        $data = $this->validated($request);
        VehicleCategory::create([
            'tenant_id' => $tid,
            'name'      => $data['name'],
            'slug'      => VehicleCategory::uniqueSlug($tid, $data['name']),
            'icon'      => $data['icon'],
            'status'    => $data['status'],
        ]);
        ActivityLog::record('created', 'categories', null, 'Categoría: ' . $data['name']);
        Session::flash('success', 'Categoría creada.');
        $this->redirect('/admin/categories');
    }

    public function update(Request $request, string $id): void
    {
        if (!can('catalog.manage')) { $this->abort(403); }
        $tid = $this->tenantId();
        VehicleCategory::findOrFail((int) $id, $tid);
        $data = $this->validated($request);
        VehicleCategory::update((int) $id, $tid, [
            'name'   => $data['name'],
            'slug'   => VehicleCategory::uniqueSlug($tid, $data['name'], (int) $id),
            'icon'   => $data['icon'],
            'status' => $data['status'],
        ]);
        ActivityLog::record('updated', 'categories', (int) $id, 'Categoría actualizada');
        Session::flash('success', 'Categoría actualizada.');
        $this->redirect('/admin/categories');
    }

    public function destroy(Request $request, string $id): void
    {
        if (!can('catalog.manage')) { $this->abort(403); }
        $tid = $this->tenantId();
        VehicleCategory::findOrFail((int) $id, $tid);
        VehicleCategory::delete((int) $id, $tid); // FK sets vehicles.category_id NULL
        ActivityLog::record('deleted', 'categories', (int) $id, 'Categoría eliminada');
        Session::flash('success', 'Categoría eliminada.');
        $this->redirect('/admin/categories');
    }

    protected function validated(Request $request): array
    {
        $v = $this->validateOrBack($request->all(), [
            'name'   => 'required|max:80',
            'status' => 'required|in:active,inactive',
        ], '/admin/categories');
        return [
            'name'   => $v['name'],
            'icon'   => $request->str('icon') ?: 'car',
            'status' => $v['status'],
        ];
    }
}
