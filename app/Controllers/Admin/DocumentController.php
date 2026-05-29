<?php
namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Database;

class DocumentController extends AdminController
{
    public function index(Request $request): void
    {
        $tid = $this->tenantId();
        $filter = $request->str('status'); // expired | soon | valid | ''
        $today = strtotime('today');
        $rows = [];

        $docLabels = [
            'insurance_expires'  => 'Seguro',
            'marbete_expires'    => 'Marbete',
            'plate_expires'      => 'Matrícula',
            'inspection_expires' => 'Inspección',
        ];

        foreach (Database::select(
            "SELECT id, brand, model, plate_number, insurance_expires, marbete_expires, plate_expires, inspection_expires
               FROM vehicles WHERE tenant_id=:t AND deleted_at IS NULL", ['t'=>$tid]
        ) as $v) {
            foreach ($docLabels as $field => $label) {
                if (empty($v[$field])) continue;
                $rows[] = [
                    'kind'   => 'vehicle',
                    'entity' => $v['brand'].' '.$v['model'].' · '.($v['plate_number'] ?? 's/p'),
                    'doc'    => $label,
                    'date'   => $v[$field],
                    'url'    => url('/admin/vehicles/show/'.$v['id']),
                ];
            }
        }
        foreach (Database::select(
            "SELECT id, first_name, last_name, license_expiration FROM customers
              WHERE tenant_id=:t AND deleted_at IS NULL AND license_expiration IS NOT NULL", ['t'=>$tid]
        ) as $c) {
            $rows[] = [
                'kind'   => 'customer',
                'entity' => trim($c['first_name'].' '.$c['last_name']),
                'doc'    => 'Licencia de conducir',
                'date'   => $c['license_expiration'],
                'url'    => url('/admin/customers/show/'.$c['id']),
            ];
        }

        // Compute status + days, then sort and filter
        foreach ($rows as &$r) {
            $days = (int) floor((strtotime($r['date']) - $today) / 86400);
            $r['days'] = $days;
            $r['status'] = $days < 0 ? 'expired' : ($days <= 30 ? 'soon' : 'valid');
        }
        unset($r);
        usort($rows, fn($a,$b) => $a['days'] <=> $b['days']);

        $counts = ['expired'=>0,'soon'=>0,'valid'=>0];
        foreach ($rows as $r) { $counts[$r['status']]++; }

        $list = $filter ? array_values(array_filter($rows, fn($r)=>$r['status']===$filter)) : $rows;

        $this->renderAdmin('admin/documents/index', [
            'title'   => 'Vencimientos · Kyros',
            'active'  => 'documents',
            'rows'    => $list,
            'counts'  => $counts,
            'filter'  => $filter,
            'breadcrumbs' => [['label'=>'Dashboard','url'=>url('/admin/dashboard')],['label'=>'Vencimientos']],
        ]);
    }
}
