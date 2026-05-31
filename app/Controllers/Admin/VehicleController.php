<?php
namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Core\Database;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\Location;
use App\Models\ActivityLog;
use App\Services\FileUploader;

class VehicleController extends AdminController
{
    public function index(Request $request): void
    {
        $tid = $this->tenantId();
        $filters = [
            'status'      => $request->str('status'),
            'category_id' => $request->int('category_id'),
            'location_id' => $request->int('location_id'),
            'search'      => $request->str('search'),
        ];
        $this->renderAdmin('admin/vehicles/index', [
            'title'      => 'Flotilla · Kyros Rent Car',
            'active'     => 'vehicles',
            'vehicles'   => Vehicle::listForTenant($tid, $filters),
            'categories' => VehicleCategory::forTenant($tid),
            'locations'  => Location::activeForTenant($tid),
            'filters'    => $filters,
            'statusCounts' => Vehicle::statusCounts($tid),
            'breadcrumbs'  => [['label'=>'Dashboard','url'=>url('/admin/dashboard')],['label'=>'Flotilla']],
        ]);
    }

    public function show(Request $request, string $id): void
    {
        $tid = $this->tenantId();
        $vehicle = Vehicle::findOrFail((int) $id, $tid);
        $images = Database::select(
            "SELECT path FROM vehicle_images WHERE tenant_id=:t AND vehicle_id=:v ORDER BY is_main DESC, sort_order ASC",
            ['t'=>$tid,'v'=>(int)$id]
        );
        $gallery = array_column($images, 'path');
        if (empty($gallery) && !empty($vehicle['main_image'])) $gallery = [$vehicle['main_image']];

        // tenant_id check is defense-in-depth — the category is already
        // guaranteed to belong to the tenant via the vehicle's FK, but we
        // scope here too so a tampered category_id can't leak names.
        $vehicle['category_name'] = $vehicle['category_id']
            ? Database::scalar("SELECT name FROM vehicle_categories WHERE id=:c AND tenant_id=:t",
                ['c'=>$vehicle['category_id'], 't'=>$tid])
            : null;
        $features = $vehicle['features'] ? (json_decode($vehicle['features'], true) ?: []) : [];

        $reservations = Database::select(
            "SELECT r.reservation_code, r.start_datetime, r.end_datetime, r.status, r.total_amount,
                    COALESCE(CONCAT(c.first_name,' ',c.last_name), r.lead_name) AS customer_name
               FROM reservations r LEFT JOIN customers c ON c.id=r.customer_id
              WHERE r.tenant_id=:t AND r.vehicle_id=:v AND r.deleted_at IS NULL
              ORDER BY r.start_datetime DESC LIMIT 6",
            ['t'=>$tid,'v'=>(int)$id]
        );
        $maintenance = Database::select(
            "SELECT maintenance_type, cost, mileage, start_date, status FROM maintenance_records
              WHERE tenant_id=:t AND vehicle_id=:v ORDER BY start_date DESC LIMIT 6",
            ['t'=>$tid,'v'=>(int)$id]
        );
        $stats = [
            'rentals'   => (int) Database::scalar("SELECT COUNT(*) FROM contracts WHERE tenant_id=:t AND vehicle_id=:v AND deleted_at IS NULL", ['t'=>$tid,'v'=>(int)$id]),
            'revenue'   => (float) Database::scalar("SELECT COALESCE(SUM(total_amount),0) FROM contracts WHERE tenant_id=:t AND vehicle_id=:v AND deleted_at IS NULL", ['t'=>$tid,'v'=>(int)$id]),
            'maint_cost'=> (float) Database::scalar("SELECT COALESCE(SUM(cost),0) FROM maintenance_records WHERE tenant_id=:t AND vehicle_id=:v", ['t'=>$tid,'v'=>(int)$id]),
        ];

        $this->renderAdmin('admin/vehicles/show', [
            'title'    => $vehicle['brand'].' '.$vehicle['model'].' · Kyros Rent Car',
            'active'   => 'vehicles',
            'vehicle'  => $vehicle,
            'gallery'  => $gallery,
            'features' => $features,
            'reservations' => $reservations,
            'maintenance'  => $maintenance,
            'stats'    => $stats,
            'breadcrumbs' => [['label'=>'Flotilla','url'=>url('/admin/vehicles')],['label'=>$vehicle['brand'].' '.$vehicle['model']]],
        ]);
    }

    public function create(Request $request): void
    {
        $tid = $this->tenantId();
        if (!\App\Models\Plan::canAdd($tid, 'vehicles')) {
            Session::flash('error', 'Tu plan alcanzó el límite de vehículos. Actualiza para agregar más.');
            $this->redirect('/admin/vehicles');
        }
        $this->renderAdmin('admin/vehicles/form', [
            'title'      => 'Nuevo vehiculo · Kyros Rent Car',
            'active'     => 'vehicles',
            'vehicle'    => null,
            'images'     => [],
            'categories' => VehicleCategory::forTenant($tid),
            'locations'  => Location::activeForTenant($tid),
            'breadcrumbs'=> [['label'=>'Flotilla','url'=>url('/admin/vehicles')],['label'=>'Nuevo']],
        ]);
    }

    public function store(Request $request): void
    {
        $tid = $this->tenantId();
        if (!\App\Models\Plan::canAdd($tid, 'vehicles')) {
            Session::flash('error', 'Tu plan alcanzó el límite de vehículos. Actualiza para agregar más.');
            $this->redirect('/admin/vehicles');
        }
        $data = $this->validatedVehicle($request, '/admin/vehicles/create');

        if (Vehicle::plateExists($tid, $data['plate_number'])) {
            Session::flash('error', 'Ya existe un vehiculo con esa placa.');
            Session::flashInput($request->all());
            $this->redirect('/admin/vehicles/create');
        }

        $slug = Vehicle::uniqueSlug($tid, $data['brand'] . '-' . $data['model'] . '-' . $data['year']);

        $payload = $this->buildPayload($tid, $data, $slug);

        // Main image upload (optional)
        if ($file = $request->file('main_image')) {
            if ($path = FileUploader::image($file, 'vehicles')) {
                $payload['main_image'] = $path;
            }
        }

        $id = Vehicle::create($payload);
        if (!empty($payload['main_image'])) {
            $this->syncMainImage($tid, (int) $id, $payload['main_image']);
        }

        // Gallery images
        $this->handleGallery($request, $tid, $id);

        ActivityLog::record('created', 'vehicles', $id, 'Vehiculo creado: ' . $data['brand'] . ' ' . $data['model']);
        Session::flash('success', 'Vehiculo creado correctamente.');
        $this->redirect('/admin/vehicles');
    }

    public function edit(Request $request, string $id): void
    {
        $tid = $this->tenantId();
        $vehicle = Vehicle::findOrFail((int) $id, $tid);
        $images = Database::select(
            "SELECT * FROM vehicle_images WHERE tenant_id = :t AND vehicle_id = :v ORDER BY is_main DESC, sort_order ASC",
            ['t' => $tid, 'v' => (int) $id]
        );
        $this->renderAdmin('admin/vehicles/form', [
            'title'      => 'Editar vehiculo · Kyros Rent Car',
            'active'     => 'vehicles',
            'vehicle'    => $vehicle,
            'images'     => $images,
            'categories' => VehicleCategory::forTenant($tid),
            'locations'  => Location::activeForTenant($tid),
            'breadcrumbs'=> [['label'=>'Flotilla','url'=>url('/admin/vehicles')],['label'=>'Editar']],
        ]);
    }

    public function update(Request $request, string $id): void
    {
        $tid = $this->tenantId();
        $vehicle = Vehicle::findOrFail((int) $id, $tid);
        $data = $this->validatedVehicle($request, '/admin/vehicles/edit/' . $id);

        if (Vehicle::plateExists($tid, $data['plate_number'], (int) $id)) {
            Session::flash('error', 'Ya existe otro vehiculo con esa placa.');
            $this->redirect('/admin/vehicles/edit/' . $id);
        }

        $slug = $vehicle['slug'];
        if (slugify($data['brand'] . '-' . $data['model'] . '-' . $data['year']) !== preg_replace('/-\d+$/', '', $slug)) {
            $slug = Vehicle::uniqueSlug($tid, $data['brand'] . '-' . $data['model'] . '-' . $data['year'], (int) $id);
        }

        $payload = $this->buildPayload($tid, $data, $slug);
        unset($payload['tenant_id']); // never reassign tenant

        $uploadedMain = null;
        if ($file = $request->file('main_image')) {
            if ($path = FileUploader::image($file, 'vehicles')) {
                $payload['main_image'] = $path;
                $uploadedMain = $path;
            }
        }

        Vehicle::update((int) $id, $tid, $payload);
        if ($uploadedMain) {
            $this->syncMainImage($tid, (int) $id, $uploadedMain);
        }
        $this->handleGallery($request, $tid, (int) $id);

        ActivityLog::record('updated', 'vehicles', (int) $id, 'Vehiculo actualizado');
        Session::flash('success', 'Vehiculo actualizado.');
        $this->redirect('/admin/vehicles');
    }

    public function destroy(Request $request, string $id): void
    {
        $tid = $this->tenantId();
        Vehicle::findOrFail((int) $id, $tid);
        Vehicle::delete((int) $id, $tid);
        ActivityLog::record('deleted', 'vehicles', (int) $id, 'Vehiculo eliminado');
        Session::flash('success', 'Vehiculo eliminado.');
        $this->redirect('/admin/vehicles');
    }

    public function changeStatus(Request $request, string $id): void
    {
        $tid = $this->tenantId();
        Vehicle::findOrFail((int) $id, $tid);
        $status = $request->str('status');
        if (!in_array($status, Vehicle::STATUSES, true)) {
            Session::flash('error', 'Estado invalido.');
            $this->redirect('/admin/vehicles');
        }
        Vehicle::update((int) $id, $tid, ['status' => $status]);
        ActivityLog::record('change_status', 'vehicles', (int) $id, 'Estado cambiado a ' . $status);
        Session::flash('success', 'Estado actualizado.');
        $this->back();
    }

    public function setMainImage(Request $request, string $id): void
    {
        $tid = $this->tenantId();
        $image = Database::selectOne(
            "SELECT * FROM vehicle_images WHERE id=:id AND tenant_id=:t",
            ['id' => (int) $id, 't' => $tid]
        );
        if (!$image) {
            Session::flash('error', 'Imagen no encontrada.');
            $this->back();
        }

        $this->syncMainImage($tid, (int) $image['vehicle_id'], $image['path']);
        ActivityLog::record('updated', 'vehicles', (int) $image['vehicle_id'], 'Imagen principal actualizada');
        Session::flash('success', 'Imagen principal actualizada.');
        $this->back();
    }

    public function deleteImage(Request $request, string $id): void
    {
        $tid = $this->tenantId();
        $image = Database::selectOne(
            "SELECT * FROM vehicle_images WHERE id=:id AND tenant_id=:t",
            ['id' => (int) $id, 't' => $tid]
        );
        if (!$image) {
            Session::flash('error', 'Imagen no encontrada.');
            $this->back();
        }

        $vehicleId = (int) $image['vehicle_id'];
        Database::beginTransaction();
        try {
            Database::execute("DELETE FROM vehicle_images WHERE id=:id AND tenant_id=:t", ['id' => (int) $id, 't' => $tid]);

            $currentMain = (string) Database::scalar(
                "SELECT COALESCE(main_image, '') FROM vehicles WHERE id=:v AND tenant_id=:t",
                ['v' => $vehicleId, 't' => $tid]
            );
            if ((int) $image['is_main'] === 1 || $currentMain === (string) $image['path']) {
                $next = Database::selectOne(
                    "SELECT * FROM vehicle_images WHERE tenant_id=:t AND vehicle_id=:v ORDER BY sort_order ASC, id ASC LIMIT 1",
                    ['t' => $tid, 'v' => $vehicleId]
                );
                if ($next) {
                    Database::execute(
                        "UPDATE vehicle_images SET is_main=CASE WHEN id=:id THEN 1 ELSE 0 END WHERE tenant_id=:t AND vehicle_id=:v",
                        ['id' => (int) $next['id'], 't' => $tid, 'v' => $vehicleId]
                    );
                    Database::execute(
                        "UPDATE vehicles SET main_image=:p WHERE id=:v AND tenant_id=:t",
                        ['p' => $next['path'], 'v' => $vehicleId, 't' => $tid]
                    );
                } else {
                    Database::execute(
                        "UPDATE vehicles SET main_image=NULL WHERE id=:v AND tenant_id=:t",
                        ['v' => $vehicleId, 't' => $tid]
                    );
                }
            }
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            throw $e;
        }

        ActivityLog::record('updated', 'vehicles', $vehicleId, 'Imagen eliminada');
        Session::flash('success', 'Imagen eliminada.');
        $this->back();
    }

    // ---- helpers --------------------------------------------------------

    protected function validatedVehicle(Request $request, string $back): array
    {
        return $this->validateOrBack($request->all(), [
            'brand'        => 'required|max:60',
            'model'        => 'required|max:80',
            'year'         => 'integer|min:1950|max:2100',
            'plate_number' => 'max:20',
            'transmission' => 'required|in:manual,automatic',
            'fuel_type'    => 'required|in:gasoline,diesel,electric,hybrid,gas',
            'daily_price'  => 'required|numeric|min:0',
            'status'       => 'required|in:' . implode(',', Vehicle::STATUSES),
        ], $back);
    }

    protected function buildPayload(int $tid, array $data, string $slug): array
    {
        $features = array_filter(array_map('trim', explode(',', (string) ($_POST['features'] ?? ''))));
        return [
            'tenant_id'        => $tid,
            'brand'            => $data['brand'],
            'model'            => $data['model'],
            'version'          => $_POST['version'] ?? null,
            'year'             => (int) ($data['year'] ?? date('Y')),
            'plate_number'     => $data['plate_number'] ?: null,
            'vin'              => ($_POST['vin'] ?? '') ?: null,
            'color'            => $_POST['color'] ?? null,
            'category_id'      => !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null,
            'location_id'      => !empty($_POST['location_id']) ? (int) $_POST['location_id'] : null,
            'transmission'     => $data['transmission'],
            'fuel_type'        => $data['fuel_type'],
            'mileage'          => (int) ($_POST['mileage'] ?? 0),
            'passengers'       => (int) ($_POST['passengers'] ?? 5),
            'doors'            => (int) ($_POST['doors'] ?? 4),
            'luggage_capacity' => (int) ($_POST['luggage_capacity'] ?? 2),
            'daily_price'      => (float) $data['daily_price'],
            'weekly_price'     => ($_POST['weekly_price'] ?? '') !== '' ? (float) $_POST['weekly_price'] : null,
            'monthly_price'    => ($_POST['monthly_price'] ?? '') !== '' ? (float) $_POST['monthly_price'] : null,
            'deposit_amount'   => (float) ($_POST['deposit_amount'] ?? 0),
            'insurance_price'  => (float) ($_POST['insurance_price'] ?? 0),
            'status'           => $data['status'],
            'description'      => $_POST['description'] ?? null,
            'features'         => $features ? json_encode(array_values($features), JSON_UNESCAPED_UNICODE) : null,
            'insurance_expires'     => ($_POST['insurance_expires'] ?? '') ?: null,
            'marbete_expires'       => ($_POST['marbete_expires'] ?? '') ?: null,
            'plate_expires'         => ($_POST['plate_expires'] ?? '') ?: null,
            'inspection_expires'    => ($_POST['inspection_expires'] ?? '') ?: null,
            'soat_expires'          => ($_POST['soat_expires'] ?? '') ?: null,
            'tecnomecanica_expires' => ($_POST['tecnomecanica_expires'] ?? '') ?: null,
            'is_featured'      => isset($_POST['is_featured']) ? 1 : 0,
            'is_public'        => isset($_POST['is_public']) ? 1 : 0,
            'slug'             => $slug,
        ];
    }

    protected function handleGallery(Request $request, int $tid, int $vehicleId): void
    {
        $paths = FileUploader::imagesFromField('gallery', 'vehicles');
        if (!$paths) {
            return;
        }

        $mainPath = (string) Database::scalar(
            "SELECT COALESCE(main_image, '') FROM vehicles WHERE id=:v AND tenant_id=:t",
            ['v' => $vehicleId, 't' => $tid]
        );
        $hasGalleryMain = (int) Database::scalar(
            "SELECT COUNT(*) FROM vehicle_images WHERE tenant_id=:t AND vehicle_id=:v AND is_main=1",
            ['t' => $tid, 'v' => $vehicleId]
        ) > 0;
        $sort = (int) Database::scalar(
            "SELECT COALESCE(MAX(sort_order), -1) FROM vehicle_images WHERE tenant_id=:t AND vehicle_id=:v",
            ['t' => $tid, 'v' => $vehicleId]
        );

        foreach ($paths as $index => $path) {
            $makeMain = $mainPath === '' && !$hasGalleryMain && $index === 0;
            Database::execute(
                "INSERT INTO vehicle_images (tenant_id, vehicle_id, path, is_main, sort_order) VALUES (:t,:v,:p,:m,:o)",
                ['t' => $tid, 'v' => $vehicleId, 'p' => $path, 'm' => $makeMain ? 1 : 0, 'o' => ++$sort]
            );
            if ($makeMain) {
                Database::execute(
                    "UPDATE vehicles SET main_image=:p WHERE id=:v AND tenant_id=:t",
                    ['p' => $path, 'v' => $vehicleId, 't' => $tid]
                );
                $mainPath = $path;
                $hasGalleryMain = true;
            }
        }
    }

    protected function syncMainImage(int $tid, int $vehicleId, string $path): void
    {
        Database::beginTransaction();
        try {
            Database::execute(
                "UPDATE vehicle_images SET is_main=0 WHERE tenant_id=:t AND vehicle_id=:v",
                ['t' => $tid, 'v' => $vehicleId]
            );
            $existing = Database::selectOne(
                "SELECT id FROM vehicle_images WHERE tenant_id=:t AND vehicle_id=:v AND path=:p",
                ['t' => $tid, 'v' => $vehicleId, 'p' => $path]
            );
            if ($existing) {
                Database::execute(
                    "UPDATE vehicle_images SET is_main=1, sort_order=0 WHERE id=:id AND tenant_id=:t",
                    ['id' => (int) $existing['id'], 't' => $tid]
                );
            } else {
                Database::execute(
                    "INSERT INTO vehicle_images (tenant_id, vehicle_id, path, is_main, sort_order) VALUES (:t,:v,:p,1,0)",
                    ['t' => $tid, 'v' => $vehicleId, 'p' => $path]
                );
            }
            Database::execute(
                "UPDATE vehicles SET main_image=:p WHERE id=:v AND tenant_id=:t",
                ['p' => $path, 'v' => $vehicleId, 't' => $tid]
            );
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            throw $e;
        }
    }
}
