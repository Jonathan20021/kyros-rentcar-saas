<?php
namespace App\Controllers\PublicSite;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Core\Database;
use App\Models\Tenant;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\Reservation;
use App\Models\Notification;
use App\Services\FileUploader;
use App\Models\PromoCode;

/**
 * Public tenant storefront served at /r/{slug}. All queries are scoped to the
 * resolved tenant; suspended/inactive tenants are not browsable.
 */
class StorefrontController extends Controller
{
    /** Resolve a browsable tenant by slug or 404. */
    protected function resolveTenant(string $slug): array
    {
        $tenant = Tenant::findBySlug($slug);
        if (!$tenant || in_array($tenant['status'], ['suspended','inactive'], true)) {
            $this->abort(404, 'Esta rent car no esta disponible.');
        }
        return $tenant;
    }

    public function show(Request $request, string $slug): void
    {
        $tenant = $this->resolveTenant($slug);
        $tid = (int) $tenant['id'];

        $filters = [
            'category_id'  => $request->int('category_id'),
            'transmission' => $request->str('transmission'),
            'fuel_type'    => $request->str('fuel_type'),
            'passengers'   => $request->int('passengers'),
            'price_min'    => $request->float('price_min'),
            'price_max'    => $request->float('price_max'),
            'search'       => $request->str('search'),
            'sort'         => $request->str('sort'),
        ];
        $start = $request->str('start');
        $end   = $request->str('end');

        $vehicles = Vehicle::publicList($tid, $filters);
        // Full (unfiltered) public fleet powers the marketing chrome (hero stats,
        // brand tiles, spotlight, fleet preview) so applying a catalog filter never
        // guts those sections. The filtered $vehicles drives only the catalog grid.
        $hasFilters = (bool) array_filter([
            $filters['category_id'], $filters['transmission'], $filters['fuel_type'],
            $filters['passengers'], $filters['price_min'], $filters['price_max'], $filters['search'],
        ]);
        $allVehicles = $hasFilters ? Vehicle::publicList($tid, []) : $vehicles;

        // If a date range is provided, annotate availability.
        if ($start && $end && strtotime($start) && strtotime($end) && strtotime($end) > strtotime($start)) {
            foreach ($vehicles as &$v) {
                $v['available_in_range'] = Vehicle::isAvailable($tid, (int) $v['id'], $start . ' 00:00:00', $end . ' 23:59:59');
            }
            unset($v);
        }

        $this->view('public/storefront/show', [
            'title'       => $tenant['name'] . ' · Renta de vehiculos',
            'metaDescription' => $tenant['description'] ?? '',
            'bodyClass'   => 'lux bg-[#0a0a0a] text-white',
            'tenant'      => $tenant,
            'vehicles'    => $vehicles,
            'allVehicles' => $allVehicles,
            'categories'  => VehicleCategory::forTenant($tid),
            'filters'     => $filters,
            'histogram'   => Vehicle::priceHistogram($tid),
            'rangeStart'  => $start,
            'rangeEnd'    => $end,
        ], 'public');
    }

    public function vehicle(Request $request, string $slug, string $vehicleSlug): void
    {
        $tenant = $this->resolveTenant($slug);
        $tid = (int) $tenant['id'];
        $vehicle = Vehicle::findBySlug($tid, $vehicleSlug);
        if (!$vehicle || !$vehicle['is_public']) {
            $this->abort(404, 'Vehiculo no encontrado.');
        }
        $images = Database::select(
            "SELECT path FROM vehicle_images WHERE tenant_id = :t AND vehicle_id = :v ORDER BY is_main DESC, sort_order ASC",
            ['t' => $tid, 'v' => $vehicle['id']]
        );
        if (!empty($vehicle['main_image'])) {
            $paths = array_column($images, 'path');
            if (!in_array($vehicle['main_image'], $paths, true)) {
                array_unshift($images, ['path' => $vehicle['main_image']]);
            }
        }
        $features = $vehicle['features'] ? (json_decode($vehicle['features'], true) ?: []) : [];

        $this->view('public/storefront/vehicle', [
            'title'    => $vehicle['brand'] . ' ' . $vehicle['model'] . ' · ' . $tenant['name'],
            'bodyClass'=> 'lux bg-[#0a0a0a] text-white',
            'tenant'   => $tenant,
            'vehicle'  => $vehicle,
            'images'   => $images,
            'features' => $features,
        ], 'public');
    }

    public function reserveForm(Request $request, string $slug, string $vehicleSlug): void
    {
        $tenant = $this->resolveTenant($slug);
        $tid = (int) $tenant['id'];
        $vehicle = Vehicle::findBySlug($tid, $vehicleSlug);
        if (!$vehicle || !$vehicle['is_public']) {
            $this->abort(404, 'Vehiculo no encontrado.');
        }
        $extras = Database::select(
            "SELECT * FROM extras WHERE tenant_id = :t AND status='active' ORDER BY name",
            ['t' => $tid]
        );
        $this->view('public/storefront/reserve', [
            'title'   => 'Reservar ' . $vehicle['brand'] . ' ' . $vehicle['model'],
            'bodyClass'=> 'lux bg-[#0a0a0a] text-white',
            'tenant'  => $tenant,
            'vehicle' => $vehicle,
            'extras'  => $extras,
            'rangeStart' => $request->str('start'),
            'rangeEnd'   => $request->str('end'),
            'publicPromos' => PromoCode::publicForTenant($tid),
        ], 'public');
    }

    /** AJAX endpoint to validate a promo code (returns discount in money terms). */
    public function promoCheck(Request $request, string $slug): void
    {
        $tenant = $this->resolveTenant($slug);
        $tid = (int) $tenant['id'];
        $code     = trim((string) $request->str('code'));
        $subtotal = max(0.0, (float) $request->str('subtotal'));
        if ($code === '') {
            $this->json(['valid' => false, 'message' => 'Indica un código.'], 422);
        }
        $promo = PromoCode::findByCode($tid, $code);
        if (!$promo || !PromoCode::isUsable($promo, $subtotal)) {
            $this->json([
                'valid' => false,
                'message' => $promo ? 'El código no aplica (vencido, agotado o monto mínimo).' : 'Código inválido.',
            ], 422);
        }
        $discount = PromoCode::discountFor($promo, $subtotal);
        $this->json([
            'valid'      => true,
            'code'       => $promo['code'],
            'type'       => $promo['discount_type'],
            'value'      => (float) $promo['discount_value'],
            'discount'   => $discount,
            'message'    => sprintf('Aplicado: %s', $promo['discount_type'] === 'percent'
                ? rtrim(rtrim(number_format((float)$promo['discount_value'],2),'0'),'.').'% de descuento'
                : 'RD$ ' . number_format($discount, 2) . ' de descuento'),
        ]);
    }

    public function reserveStore(Request $request, string $slug, string $vehicleSlug): void
    {
        $tenant = $this->resolveTenant($slug);
        $tid = (int) $tenant['id'];
        $vehicle = Vehicle::findBySlug($tid, $vehicleSlug);
        if (!$vehicle || !$vehicle['is_public']) {
            $this->abort(404, 'Vehiculo no encontrado.');
        }

        $back = '/r/' . $slug . '/reservar/' . $vehicleSlug;
        $data = $this->validateOrBack($request->all(), [
            'lead_name'  => 'required|min:3|max:150',
            'lead_phone' => 'required|max:30',
            'lead_email' => 'email|max:150',
            'start_date' => 'required|date',
            'end_date'   => 'required|date',
        ], $back);

        $start = $data['start_date'] . ' ' . ($request->str('start_time', '09:00')) . ':00';
        $end   = $data['end_date'] . ' ' . ($request->str('end_time', '09:00')) . ':00';

        // Date sanity
        if (strtotime($end) <= strtotime($start)) {
            Session::flash('error', 'La fecha de devolucion debe ser posterior a la de inicio.');
            Session::flashInput($request->all());
            $this->redirect($back);
        }
        if (strtotime($start) < strtotime('today')) {
            Session::flash('error', 'La fecha de inicio no puede estar en el pasado.');
            Session::flashInput($request->all());
            $this->redirect($back);
        }

        // Business rule: vehicle must be available in the range.
        if (!Vehicle::isAvailable($tid, (int) $vehicle['id'], $start, $end)) {
            Session::flash('error', 'Lo sentimos, el vehiculo no esta disponible en esas fechas.');
            Session::flashInput($request->all());
            $this->redirect($back);
        }

        // Pricing
        $days = max(1, (int) ceil((strtotime($end) - strtotime($start)) / 86400));
        $dailyRate = (float) $vehicle['daily_price'];
        $subtotal  = $dailyRate * $days;

        // Extras
        $extrasTotal = 0.0;
        $selectedExtras = [];
        foreach ((array) $request->input('extras', []) as $extraId) {
            $ex = Database::selectOne("SELECT * FROM extras WHERE id = :id AND tenant_id = :t AND status='active'", ['id' => (int) $extraId, 't' => $tid]);
            if (!$ex) continue;
            $line = $ex['charge_type'] === 'per_day' ? $ex['price'] * $days : (float) $ex['price'];
            $extrasTotal += $line;
            $selectedExtras[] = ['extra' => $ex, 'line' => $line];
        }

        // Promo code (optional)
        $promo = null;
        $discount = 0.0;
        $rawCode = trim((string) $request->str('promo_code'));
        if ($rawCode !== '') {
            $candidate = PromoCode::findByCode($tid, $rawCode);
            if ($candidate && PromoCode::isUsable($candidate, $subtotal + $extrasTotal)) {
                $discount = PromoCode::discountFor($candidate, $subtotal + $extrasTotal);
                $promo = $candidate;
            }
        }

        $taxRate = (float) $tenant['tax_rate'];
        $taxable = max(0, $subtotal + $extrasTotal - $discount);
        $tax = round($taxable * ($taxRate / 100), 2);
        $deposit = (float) $vehicle['deposit_amount'];
        $total = round($taxable + $tax, 2);

        try {
            Database::beginTransaction();
            $code = Reservation::nextCode($tid);
            $resId = Reservation::create([
                'tenant_id'       => $tid,
                'reservation_code'=> $code,
                'customer_id'     => null,
                'vehicle_id'      => (int) $vehicle['id'],
                'start_datetime'  => $start,
                'end_datetime'    => $end,
                'pickup_location' => $request->str('pickup_location') ?: null,
                'return_location' => $request->str('return_location') ?: null,
                'daily_rate'      => $dailyRate,
                'days_count'      => $days,
                'subtotal'        => $subtotal,
                'discount_amount' => $discount,
                'promo_code_id'   => $promo['id'] ?? null,
                'tax_amount'      => $tax,
                'deposit_amount'  => $deposit,
                'extras_total'    => $extrasTotal,
                'total_amount'    => $total,
                'status'          => 'pending',
                'source'          => 'public',
                'lead_name'       => $data['lead_name'],
                'lead_phone'      => $data['lead_phone'],
                'lead_whatsapp'   => $request->str('lead_whatsapp') ?: $data['lead_phone'],
                'lead_email'      => $request->str('lead_email') ? strtolower($request->str('lead_email')) : null,
                'lead_document'   => $request->str('lead_document') ?: null,
                'preferred_contact'=> $request->str('preferred_contact', 'whatsapp'),
                'notes'           => $request->str('notes') ?: null,
            ]);

            foreach ($selectedExtras as $se) {
                Database::execute(
                    "INSERT INTO reservation_extras (tenant_id, reservation_id, extra_id, name, price, quantity, charge_type, line_total)
                     VALUES (:t,:r,:e,:n,:p,1,:ct,:lt)",
                    ['t'=>$tid,'r'=>$resId,'e'=>$se['extra']['id'],'n'=>$se['extra']['name'],'p'=>$se['extra']['price'],'ct'=>$se['extra']['charge_type'],'lt'=>$se['line']]
                );
            }

            // Optional license upload
            if ($f = $request->file('lead_license')) {
                if ($p = FileUploader::document($f, 'reservations')) {
                    Database::execute("UPDATE reservations SET lead_license = :p WHERE id = :id AND tenant_id = :t", ['p'=>$p,'id'=>$resId,'t'=>$tid]);
                }
            }

            // Internal notification for the tenant.
            Notification::create($tid, null, 'Nueva reserva publica',
                $data['lead_name'] . ' solicito ' . $vehicle['brand'] . ' ' . $vehicle['model'] . ' (' . $code . ')',
                'reservation', '/admin/reservations');

            if ($promo) {
                PromoCode::incrementUse((int) $promo['id'], $tid);
            }

            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            \App\Core\Logger::error('Public reservation failed: ' . $e->getMessage());
            Session::flash('error', 'No se pudo procesar la reserva. Intenta de nuevo.');
            $this->redirect($back);
        }

        // Confirmation email to the customer (if email provided)
        $leadEmail = $request->str('lead_email') ? strtolower($request->str('lead_email')) : null;
        if ($leadEmail) {
            try {
                // Prefer the tenant's customizable template; fall back to the built-in body.
                $sent = \App\Services\Mailer::fromTemplate('reservation_received', $leadEmail, $tenant ?? [], [
                    'customer' => $data['lead_name'], 'vehicle' => $vehicle['brand'].' '.$vehicle['model'],
                    'code' => $code, 'start' => format_date($start), 'end' => format_date($end), 'total' => money($total),
                ], ['label'=>'Ver vehículos','url'=>abs_url('/r/'.$slug)]);
                if (!$sent) {
                    $body = '<p>Hola <strong>'.e($data['lead_name']).'</strong>,</p>'
                        . '<p>Recibimos tu solicitud de reserva. Pronto te contactaremos para confirmar disponibilidad y pago.</p>'
                        . '<table style="margin:12px 0;font-size:14px;width:100%">'
                        . '<tr><td style="color:#6b7280;padding:3px 0">Código</td><td style="font-weight:600;text-align:right">'.e($code).'</td></tr>'
                        . '<tr><td style="color:#6b7280;padding:3px 0">Vehículo</td><td style="font-weight:600;text-align:right">'.e($vehicle['brand'].' '.$vehicle['model']).'</td></tr>'
                        . '<tr><td style="color:#6b7280;padding:3px 0">Período</td><td style="font-weight:600;text-align:right">'.format_date($start).' → '.format_date($end).' ('.$days.' días)</td></tr>'
                        . '<tr><td style="color:#6b7280;padding:3px 0">Total estimado</td><td style="font-weight:700;text-align:right">'.money($total).'</td></tr></table>';
                    \App\Services\Mailer::send($leadEmail, 'Reserva recibida · '.$tenant['name'],
                        \App\Services\Mailer::layout('¡Reserva recibida!', $body, $tenant, ['label'=>'Ver vehículos','url'=>abs_url('/r/'.$slug)]), $tenant['email'] ?? null);
                }
            } catch (\Throwable $e) { \App\Core\Logger::error('reservation mail: '.$e->getMessage()); }
        }

        Session::set('_last_reservation', [
            'code' => $code, 'vehicle' => $vehicle['brand'] . ' ' . $vehicle['model'],
            'start' => $start, 'end' => $end, 'total' => $total, 'days' => $days,
            'name' => $data['lead_name'],
        ]);
        $this->redirect('/r/' . $slug . '/reserva/confirmacion');
    }

    public function confirmation(Request $request, string $slug): void
    {
        $tenant = $this->resolveTenant($slug);
        $reservation = Session::get('_last_reservation');
        if (!$reservation) {
            $this->redirect('/r/' . $slug);
        }
        Session::forget('_last_reservation');
        $this->view('public/storefront/confirmation', [
            'title'       => 'Reserva confirmada · ' . $tenant['name'],
            'bodyClass'   => 'lux bg-[#0a0a0a] text-white',
            'tenant'      => $tenant,
            'reservation' => $reservation,
        ], 'public');
    }

    /** AJAX availability check (Fetch). Returns JSON {available:bool}. */
    public function availability(Request $request, string $slug): void
    {
        $tenant = $this->resolveTenant($slug);
        $tid = (int) $tenant['id'];
        $vehicleId = $request->int('vehicle_id');
        $start = $request->str('start');
        $end   = $request->str('end');
        if (!$vehicleId || !strtotime($start) || !strtotime($end)) {
            $this->json(['available' => false, 'error' => 'Parametros invalidos'], 422);
        }
        $available = Vehicle::isAvailable($tid, $vehicleId, $start . ' 00:00:00', $end . ' 23:59:59');
        $this->json(['available' => $available]);
    }
}
