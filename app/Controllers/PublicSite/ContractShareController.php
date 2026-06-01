<?php
namespace App\Controllers\PublicSite;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Database;
use App\Core\Session;
use App\Core\Logger;
use App\Models\Contract;
use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\Tenant;
use App\Models\ActivityLog;
use App\Services\FileUploader;

/**
 * Public, token-protected contract viewer.
 * The 40-hex `share_token` IS the auth — long random secret, sent via the share link.
 * After resolving the contract, every downstream query is scoped by the contract's
 * OWN tenant_id, so a token cannot leak data from a different tenant.
 */
class ContractShareController extends Controller
{
    /** Render the contract for the customer. */
    public function show(Request $request, string $token): void
    {
        $contract = Contract::findByShareToken($token);
        if (!$contract) {
            $this->abort(404, 'Este enlace es inválido o fue revocado.');
        }
        $tid = (int) $contract['tenant_id'];

        // Pull related rows — every query scoped by tenant_id.
        $tenant   = Tenant::find($tid, null);
        // Public route (no auth) — set currency so money() formats in the tenant's currency.
        \App\Services\LocaleService::setCurrentCurrency($tenant['currency'] ?? null);
        $vehicle  = Vehicle::find((int) $contract['vehicle_id'], $tid);
        $customer = Customer::find((int) $contract['customer_id'], $tid);

        $photos = Database::select(
            "SELECT phase, path, note FROM contract_photos
               WHERE tenant_id = :t AND contract_id = :c
               ORDER BY phase = 'delivery' DESC, id ASC",
            ['t' => $tid, 'c' => (int) $contract['id']]
        );

        $payments = Database::select(
            "SELECT payment_code, amount, method, payment_date, status
               FROM payments WHERE tenant_id = :t AND contract_id = :c
              ORDER BY payment_date DESC, id DESC",
            ['t' => $tid, 'c' => (int) $contract['id']]
        );

        $reservation = $contract['reservation_id'] ? Database::selectOne(
            "SELECT pickup_location, return_location, notes
               FROM reservations WHERE id = :r AND tenant_id = :t",
            ['r' => (int) $contract['reservation_id'], 't' => $tid]
        ) : null;

        $this->view('public/contract/show', [
            'title'       => 'Contrato ' . $contract['contract_number'] . ' · ' . ($tenant['name'] ?? 'Rent car'),
            'contract'    => $contract,
            'tenant'      => $tenant,
            'vehicle'     => $vehicle,
            'customer'    => $customer,
            'photos'      => $photos,
            'payments'    => $payments,
            'reservation' => $reservation,
            'token'       => $token,
        ], 'public_minimal');
    }

    /** Stream the contract as a real PDF (dompdf) — open to anyone with the token. */
    public function pdf(Request $request, string $token): void
    {
        $contract = Contract::findByShareToken($token);
        if (!$contract) {
            $this->abort(404, 'Este enlace es inválido o fue revocado.');
        }
        $tid = (int) $contract['tenant_id'];
        $contract['tenant']   = Tenant::find($tid, null);
        \App\Services\LocaleService::setCurrentCurrency($contract['tenant']['currency'] ?? null);
        $contract['vehicle']  = Vehicle::find((int) $contract['vehicle_id'], $tid);
        $contract['customer'] = Customer::find((int) $contract['customer_id'], $tid);

        $html = \App\Core\View::renderPartial('admin/contracts/pdf_dompdf', ['c' => $contract]);
        $pdf  = \App\Services\PdfService::render($html);
        \App\Services\PdfService::stream($pdf, 'Contrato-' . $contract['contract_number'] . '.pdf');
    }

    /** Persist the customer's signature (base64 PNG). */
    public function sign(Request $request, string $token): void
    {
        $contract = Contract::findByShareToken($token);
        if (!$contract) {
            $this->abort(404, 'Este enlace es inválido o fue revocado.');
        }
        $tid = (int) $contract['tenant_id'];
        $cid = (int) $contract['id'];

        // Idempotent: refuse re-signing.
        if (!empty($contract['signed_at'])) {
            Session::flash('info', 'Este contrato ya está firmado.');
            $this->redirect('/contrato/' . $token);
        }

        // The pad now submits an SVG string (preferred — renders without GD).
        // Legacy clients may still send a PNG data URL; both are accepted.
        $payload = (string) $request->input('signature', '');
        $path = null;
        if (stripos(ltrim($payload), '<svg') === 0) {
            $path = FileUploader::saveSignatureSvg($payload, 'signatures');
        } elseif (str_starts_with($payload, 'data:image/png')) {
            $path = FileUploader::saveDataUrlPng($payload, 'signatures');
        }
        if (!$path) {
            Session::flash('error', 'La firma es inválida o demasiado grande. Inténtalo de nuevo.');
            $this->redirect('/contrato/' . $token);
        }

        try {
            Database::beginTransaction();
            Database::execute(
                "UPDATE contracts SET customer_signature = :p, signed_at = NOW(), signed_ip = :ip
                   WHERE id = :id AND tenant_id = :t",
                ['p' => $path, 'ip' => $request->ip(), 'id' => $cid, 't' => $tid]
            );
            ActivityLog::recordSystem($tid, 'signed', 'contracts', $cid,
                'Firma del cliente recibida vía enlace público (IP ' . $request->ip() . ')');
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            Logger::error('Public sign failed: ' . $e->getMessage());
            Session::flash('error', 'No se pudo guardar la firma. Intenta nuevamente.');
            $this->redirect('/contrato/' . $token);
        }

        Session::flash('success', '¡Firma recibida! Gracias por firmar tu contrato.');
        $this->redirect('/contrato/' . $token);
    }
}
