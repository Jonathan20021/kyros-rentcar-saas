<?php
namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Core\Database;
use App\Models\Reservation;
use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\Contract;
use App\Models\ActivityLog;
use App\Models\Tenant;

class ReservationController extends AdminController
{
    public function index(Request $request): void
    {
        $tid = $this->tenantId();
        $filters = ['status' => $request->str('status'), 'search' => $request->str('search')];
        $this->renderAdmin('admin/reservations/index', [
            'title'        => 'Reservas · Kyros',
            'active'       => 'reservations',
            'reservations' => Reservation::listForTenant($tid, $filters),
            'filters'      => $filters,
            'statusCounts' => Reservation::statusCounts($tid),
            'breadcrumbs'  => [['label'=>'Dashboard','url'=>url('/admin/dashboard')],['label'=>'Reservas']],
        ]);
    }

    public function calendar(Request $request): void
    {
        $this->renderAdmin('admin/reservations/calendar', [
            'title'  => 'Calendario · Kyros',
            'active' => 'reservations',
            'breadcrumbs' => [['label'=>'Reservas','url'=>url('/admin/reservations')],['label'=>'Calendario']],
        ]);
    }

    /** FullCalendar JSON feed. */
    public function events(Request $request): void
    {
        $this->json(Reservation::calendarEvents($this->tenantId()));
    }

    public function create(Request $request): void
    {
        $tid = $this->tenantId();
        $this->renderAdmin('admin/reservations/form', [
            'title'      => 'Nueva reserva · Kyros',
            'active'     => 'reservations',
            'customers'  => Customer::listForTenant($tid),
            'vehicles'   => Vehicle::listForTenant($tid, ['status' => '']),
            'extras'     => Database::select("SELECT * FROM extras WHERE tenant_id=:t AND status='active' ORDER BY name", ['t'=>$tid]),
            'tenant'     => Tenant::find($tid, null),
            'preVehicle' => $request->int('vehicle') ?: 0,
            'breadcrumbs'=> [['label'=>'Reservas','url'=>url('/admin/reservations')],['label'=>'Nueva']],
        ]);
    }

    public function store(Request $request): void
    {
        $tid = $this->tenantId();
        $tenant = Tenant::find($tid, null);
        $back = '/admin/reservations/create';

        $data = $this->validateOrBack($request->all(), [
            'vehicle_id' => 'required|integer',
            'start_date' => 'required|date',
            'end_date'   => 'required|date',
        ], $back);

        $vehicle = Vehicle::find((int) $data['vehicle_id'], $tid);
        if (!$vehicle) { Session::flash('error','Vehiculo invalido.'); $this->redirect($back); }

        // Resolve customer: existing or quick-create
        $customerId = $request->int('customer_id');
        if (!$customerId && $request->str('new_customer_name')) {
            $customerId = Customer::create([
                'tenant_id'  => $tid,
                'first_name' => $request->str('new_customer_name'),
                'phone'      => $request->str('new_customer_phone') ?: null,
                'email'      => $request->str('new_customer_email') ? strtolower($request->str('new_customer_email')) : null,
                'document_type' => 'cedula',
                'status'     => 'active',
            ]);
        }
        if ($customerId && Customer::isBlacklisted($tid, $customerId)) {
            Session::flash('error','El cliente esta en lista negra. No se puede reservar.');
            $this->redirect($back);
        }

        $start = $data['start_date'] . ' ' . ($request->str('start_time','09:00')) . ':00';
        $end   = $data['end_date'] . ' ' . ($request->str('end_time','09:00')) . ':00';
        if (strtotime($end) <= strtotime($start)) {
            Session::flash('error','La devolucion debe ser posterior al inicio.');
            $this->redirect($back);
        }
        if (!Vehicle::isAvailable($tid, (int) $vehicle['id'], $start, $end)) {
            Session::flash('error','El vehiculo no esta disponible en ese rango.');
            $this->redirect($back);
        }

        $days = max(1, (int) ceil((strtotime($end) - strtotime($start)) / 86400));
        $dailyRate = $request->float('daily_rate') ?: (float) $vehicle['daily_price'];
        $subtotal = $dailyRate * $days;
        $discount = $request->float('discount_amount');

        // extras
        $extrasTotal = 0.0; $selected = [];
        foreach ((array) $request->input('extras', []) as $exId) {
            $ex = Database::selectOne("SELECT * FROM extras WHERE id=:id AND tenant_id=:t AND status='active'", ['id'=>(int)$exId,'t'=>$tid]);
            if (!$ex) continue;
            $line = $ex['charge_type'] === 'per_day' ? $ex['price'] * $days : (float) $ex['price'];
            $extrasTotal += $line; $selected[] = ['ex'=>$ex,'line'=>$line];
        }

        $taxable = max(0, $subtotal - $discount) + $extrasTotal;
        $tax = round($taxable * ((float)$tenant['tax_rate']/100), 2);
        $deposit = (float) $vehicle['deposit_amount'];
        $total = round(max(0, $subtotal - $discount) + $extrasTotal + $tax, 2);

        try {
            Database::beginTransaction();
            $code = Reservation::nextCode($tid);
            $resId = Reservation::create([
                'tenant_id'=>$tid,'reservation_code'=>$code,'customer_id'=>$customerId ?: null,
                'vehicle_id'=>(int)$vehicle['id'],'start_datetime'=>$start,'end_datetime'=>$end,
                'pickup_location'=>$request->str('pickup_location') ?: null,'return_location'=>$request->str('return_location') ?: null,
                'daily_rate'=>$dailyRate,'days_count'=>$days,'subtotal'=>$subtotal,'discount_amount'=>$discount,
                'tax_amount'=>$tax,'deposit_amount'=>$deposit,'extras_total'=>$extrasTotal,'total_amount'=>$total,
                'status'=>$request->str('status','confirmed'),'source'=>'internal',
                'lead_name'=>$request->str('new_customer_name') ?: null,
                'notes'=>$request->str('notes') ?: null,'created_by'=>\App\Core\Auth::id(),
            ]);
            foreach ($selected as $s) {
                Database::execute("INSERT INTO reservation_extras (tenant_id,reservation_id,extra_id,name,price,quantity,charge_type,line_total) VALUES (:t,:r,:e,:n,:p,1,:ct,:lt)",
                    ['t'=>$tid,'r'=>$resId,'e'=>$s['ex']['id'],'n'=>$s['ex']['name'],'p'=>$s['ex']['price'],'ct'=>$s['ex']['charge_type'],'lt'=>$s['line']]);
            }
            // confirmed reservation blocks the vehicle
            if (in_array($request->str('status','confirmed'), ['confirmed','in_progress'], true)) {
                Vehicle::update((int)$vehicle['id'], $tid, ['status'=>'reserved']);
            }
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            \App\Core\Logger::error('Internal reservation failed: '.$e->getMessage());
            Session::flash('error','No se pudo crear la reserva.');
            $this->redirect($back);
        }

        ActivityLog::record('created','reservations',$resId,'Reserva interna '.$code);
        Session::flash('success','Reserva '.$code.' creada.');
        $this->redirect('/admin/reservations/show/'.$resId);
    }

    public function show(Request $request, string $id): void
    {
        $tid = $this->tenantId();
        $r = Reservation::findOrFail((int) $id, $tid);
        $vehicle = Vehicle::find((int) $r['vehicle_id'], $tid);
        $customer = $r['customer_id'] ? Customer::find((int) $r['customer_id'], $tid) : null;
        $extras = Database::select("SELECT * FROM reservation_extras WHERE tenant_id=:t AND reservation_id=:r", ['t'=>$tid,'r'=>$id]);
        $contract = Database::selectOne("SELECT * FROM contracts WHERE tenant_id=:t AND reservation_id=:r AND deleted_at IS NULL LIMIT 1", ['t'=>$tid,'r'=>$id]);
        $customers = $r['customer_id'] ? [] : Customer::listForTenant($tid);
        $this->renderAdmin('admin/reservations/show', [
            'title'=>'Reserva '.$r['reservation_code'],'active'=>'reservations',
            'r'=>$r,'vehicle'=>$vehicle,'customer'=>$customer,'extras'=>$extras,'contract'=>$contract,
            'customers'=>$customers,
            'breadcrumbs'=>[['label'=>'Reservas','url'=>url('/admin/reservations')],['label'=>$r['reservation_code']]],
        ]);
    }

    /** Assign a customer to a reservation (creates one from lead info if requested). */
    public function assignCustomer(Request $request, string $id): void
    {
        $tid = $this->tenantId();
        $r = Reservation::findOrFail((int) $id, $tid);
        $back = '/admin/reservations/show/' . $id;

        $mode = $request->str('mode'); // 'existing' | 'lead'
        $customerId = null;

        if ($mode === 'existing') {
            $customerId = $request->int('customer_id');
            if (!$customerId || !Customer::find($customerId, $tid)) {
                Session::flash('error', 'Selecciona un cliente válido.'); $this->redirect($back);
            }
        } else { // 'lead' — create a customer from the reservation lead data
            $name  = trim((string) ($r['lead_name'] ?? ''));
            if ($name === '') {
                Session::flash('error', 'La reserva no tiene datos de prospecto para crear un cliente.'); $this->redirect($back);
            }
            $parts = preg_split('/\s+/', $name, 2);
            $first = $parts[0]; $last = $parts[1] ?? null;
            $email = $r['lead_email'] ? strtolower((string) $r['lead_email']) : null;
            // Match by phone or email so we don't duplicate.
            $existing = null;
            if ($email) {
                $existing = Database::selectOne("SELECT id FROM customers WHERE tenant_id=:t AND email=:e AND deleted_at IS NULL LIMIT 1", ['t'=>$tid,'e'=>$email]);
            }
            if (!$existing && !empty($r['lead_phone'])) {
                $existing = Database::selectOne("SELECT id FROM customers WHERE tenant_id=:t AND phone=:p AND deleted_at IS NULL LIMIT 1", ['t'=>$tid,'p'=>$r['lead_phone']]);
            }
            if ($existing) {
                $customerId = (int) $existing['id'];
            } else {
                $customerId = Customer::create([
                    'tenant_id'       => $tid,
                    'first_name'      => $first,
                    'last_name'       => $last,
                    'document_type'   => 'cedula',
                    'document_number' => $r['lead_document'] ?: null,
                    'phone'           => $r['lead_phone'] ?: null,
                    'whatsapp'        => $r['lead_whatsapp'] ?: ($r['lead_phone'] ?: null),
                    'email'           => $email,
                    'status'          => 'active',
                ]);
                ActivityLog::record('created', 'customers', $customerId, 'Cliente creado desde reserva ' . $r['reservation_code']);
            }
        }

        Reservation::update((int) $id, $tid, ['customer_id' => $customerId]);
        ActivityLog::record('updated', 'reservations', (int) $id, 'Cliente asignado a ' . $r['reservation_code']);
        Session::flash('success', 'Cliente asignado a la reserva.');
        $this->redirect($back);
    }

    /** Cancel a reservation and release the vehicle if it was reserved for this one. */
    public function cancel(Request $request, string $id): void
    {
        $tid = $this->tenantId();
        $r = Reservation::findOrFail((int) $id, $tid);
        if (in_array($r['status'], ['cancelled','rejected','converted','finished'], true)) {
            Session::flash('warning', 'La reserva ya está en un estado final.');
            $this->redirect('/admin/reservations/show/' . $id);
        }
        Reservation::update((int) $id, $tid, ['status' => 'cancelled']);
        // If the vehicle was held by this reservation, free it (only if no other active reservation uses it now).
        $other = Database::scalar(
            "SELECT 1 FROM reservations WHERE tenant_id=:t AND vehicle_id=:v AND id<>:r
               AND status IN ('confirmed','in_progress') AND deleted_at IS NULL LIMIT 1",
            ['t'=>$tid,'v'=>(int)$r['vehicle_id'],'r'=>(int)$id]
        );
        if (!$other) {
            $veh = Vehicle::find((int) $r['vehicle_id'], $tid);
            if ($veh && in_array($veh['status'], ['reserved'], true)) {
                Vehicle::update((int) $r['vehicle_id'], $tid, ['status' => 'available']);
            }
        }
        ActivityLog::record('updated', 'reservations', (int) $id, 'Reserva ' . $r['reservation_code'] . ' cancelada');
        Session::flash('success', 'Reserva cancelada.');
        $this->redirect('/admin/reservations/show/' . $id);
    }

    /** Convert a reservation into an active contract (business rule #2/#3). */
    public function convert(Request $request, string $id): void
    {
        $tid = $this->tenantId();
        $r = Reservation::findOrFail((int) $id, $tid);
        if (!$r['customer_id']) { Session::flash('error','La reserva necesita un cliente asignado para generar contrato.'); $this->redirect('/admin/reservations/show/'.$id); }
        if (Database::selectOne("SELECT id FROM contracts WHERE reservation_id=:r AND tenant_id=:t AND deleted_at IS NULL", ['r'=>$id,'t'=>$tid])) {
            Session::flash('warning','Esta reserva ya tiene un contrato.'); $this->redirect('/admin/reservations/show/'.$id);
        }
        try {
            Database::beginTransaction();
            $num = Contract::nextNumber($tid);
            $cid = Contract::create([
                'tenant_id'=>$tid,'contract_number'=>$num,'reservation_id'=>(int)$id,'customer_id'=>(int)$r['customer_id'],
                'vehicle_id'=>(int)$r['vehicle_id'],'start_datetime'=>$r['start_datetime'],'end_datetime'=>$r['end_datetime'],
                'start_mileage'=>$request->int('start_mileage') ?: null,'start_fuel_level'=>$request->int('start_fuel_level') ?: 100,
                'daily_rate'=>$r['daily_rate'],'subtotal'=>$r['subtotal'],'deposit_amount'=>$r['deposit_amount'],
                'insurance_amount'=>0,'extras_total'=>$r['extras_total'],'penalties_total'=>0,'tax_amount'=>$r['tax_amount'],
                'total_amount'=>$r['total_amount'],'paid_amount'=>0,'balance_due'=>$r['total_amount'],
                'status'=>'active','created_by'=>\App\Core\Auth::id(),
            ]);
            Reservation::update((int)$id, $tid, ['status'=>'converted']);
            Vehicle::update((int)$r['vehicle_id'], $tid, ['status'=>'rented']);
            // Delivery photos (evidence at handover)
            foreach (\App\Services\FileUploader::imagesFromField('delivery_photos', 'contracts') as $p) {
                Database::execute("INSERT INTO contract_photos (tenant_id, contract_id, phase, path) VALUES (:t,:c,'delivery',:p)", ['t'=>$tid,'c'=>$cid,'p'=>$p]);
            }
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            \App\Core\Logger::error('Convert failed: '.$e->getMessage());
            Session::flash('error','No se pudo generar el contrato.');
            $this->redirect('/admin/reservations/show/'.$id);
        }

        // Contract-generated email to the customer
        try {
            $tenant = Tenant::find($tid, null);
            $cust = Customer::find((int) $r['customer_id'], $tid);
            $to = $cust['email'] ?? null;
            if ($to) {
                $veh = Vehicle::find((int) $r['vehicle_id'], $tid);
                $body = '<p>Hola <strong>'.e(trim($cust['first_name'].' '.$cust['last_name'])).'</strong>,</p>'
                    . '<p>Tu contrato de alquiler <strong>'.e($num).'</strong> fue generado.</p>'
                    . '<table style="margin:12px 0;font-size:14px;width:100%">'
                    . '<tr><td style="color:#6b7280;padding:3px 0">Vehículo</td><td style="font-weight:600;text-align:right">'.e(($veh['brand']??'').' '.($veh['model']??'')).'</td></tr>'
                    . '<tr><td style="color:#6b7280;padding:3px 0">Período</td><td style="font-weight:600;text-align:right">'.format_date($r['start_datetime']).' → '.format_date($r['end_datetime']).'</td></tr>'
                    . '<tr><td style="color:#6b7280;padding:3px 0">Total</td><td style="font-weight:700;text-align:right">'.money($r['total_amount']).'</td></tr></table>'
                    . '<p>Conserva este correo como comprobante. ¡Buen viaje!</p>';
                \App\Services\Mailer::send($to, 'Contrato '.$num.' · '.($tenant['name']??'Kyros'),
                    \App\Services\Mailer::layout('Contrato generado', $body, $tenant), $tenant['email'] ?? null);
            }
        } catch (\Throwable $e) { \App\Core\Logger::error('contract mail: '.$e->getMessage()); }

        ActivityLog::record('created','contracts',$cid,'Contrato '.$num.' desde reserva '.$r['reservation_code']);
        Session::flash('success','Contrato '.$num.' generado.');
        $this->redirect('/admin/contracts/show/'.$cid);
    }

    /** AJAX availability for the internal form. */
    public function availability(Request $request): void
    {
        $tid = $this->tenantId();
        $vid = $request->int('vehicle_id'); $s = $request->str('start'); $e = $request->str('end');
        if (!$vid || !strtotime($s) || !strtotime($e)) { $this->json(['available'=>false,'error'=>'params'], 422); }
        $this->json(['available'=>Vehicle::isAvailable($tid, $vid, $s.' 00:00:00', $e.' 23:59:59')]);
    }

    public function changeStatus(Request $request, string $id): void
    {
        $tid = $this->tenantId();
        $reservation = Reservation::findOrFail((int) $id, $tid);
        $status = $request->str('status');
        if (!in_array($status, Reservation::STATUSES, true)) {
            Session::flash('error', 'Estado invalido.');
            $this->redirect('/admin/reservations');
        }

        // Business rule: confirming blocks availability → ensure still free.
        if ($status === 'confirmed') {
            if (!Vehicle::isAvailable($tid, (int) $reservation['vehicle_id'], $reservation['start_datetime'], $reservation['end_datetime'], (int) $id)) {
                Session::flash('error', 'El vehiculo ya no esta disponible en ese rango.');
                $this->redirect('/admin/reservations');
            }
            Vehicle::update((int) $reservation['vehicle_id'], $tid, ['status' => 'reserved']);
        }

        Reservation::update((int) $id, $tid, ['status' => $status]);

        // Notify the customer when confirmed.
        if ($status === 'confirmed') {
            $to = $reservation['lead_email'] ?? null;
            if (!$to && $reservation['customer_id']) {
                $to = Database::scalar("SELECT email FROM customers WHERE id=:c AND tenant_id=:t", ['c'=>$reservation['customer_id'],'t'=>$tid]) ?: null;
            }
            if ($to) {
                try {
                    $tenant = Tenant::find($tid, null);
                    $veh = Vehicle::find((int) $reservation['vehicle_id'], $tid);
                    $body = '<p>¡Buenas noticias! Tu reserva <strong>'.e($reservation['reservation_code']).'</strong> fue <strong>confirmada</strong>.</p>'
                        . '<table style="margin:12px 0;font-size:14px;width:100%">'
                        . '<tr><td style="color:#6b7280;padding:3px 0">Vehículo</td><td style="font-weight:600;text-align:right">'.e(($veh['brand']??'').' '.($veh['model']??'')).'</td></tr>'
                        . '<tr><td style="color:#6b7280;padding:3px 0">Período</td><td style="font-weight:600;text-align:right">'.format_date($reservation['start_datetime']).' → '.format_date($reservation['end_datetime']).'</td></tr>'
                        . '<tr><td style="color:#6b7280;padding:3px 0">Total</td><td style="font-weight:700;text-align:right">'.money($reservation['total_amount']).'</td></tr></table>'
                        . '<p>Te esperamos. ¡Gracias por elegirnos!</p>';
                    \App\Services\Mailer::send($to, 'Reserva confirmada · '.($tenant['name']??'Kyros'),
                        \App\Services\Mailer::layout('Reserva confirmada ✓', $body, $tenant), $tenant['email'] ?? null);
                } catch (\Throwable $e) { \App\Core\Logger::error('confirm mail: '.$e->getMessage()); }
            }
        }

        ActivityLog::record('change_status', 'reservations', (int) $id, 'Reserva ' . $reservation['reservation_code'] . ' → ' . $status);
        Session::flash('success', 'Estado de la reserva actualizado.');
        $this->back();
    }
}
