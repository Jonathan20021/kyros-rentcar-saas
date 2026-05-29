<?php
namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Database;

class ActivityController extends AdminController
{
    public function index(Request $request): void
    {
        $tid = $this->tenantId();
        $filters = [
            'module' => $request->str('module'),
            'action' => $request->str('action'),
            'user'   => $request->int('user'),
            'from'   => $request->str('from'),
            'to'     => $request->str('to'),
        ];

        $sql = "SELECT a.id, a.action, a.module, a.entity_id, a.description, a.created_at, a.ip_address,
                       u.id AS user_id, u.name AS user_name, u.email AS user_email, u.avatar AS user_avatar
                  FROM activity_logs a
                  LEFT JOIN users u ON u.id = a.user_id
                 WHERE a.tenant_id = :t";
        $p = ['t' => $tid];
        if ($filters['module']) { $sql .= " AND a.module = :m"; $p['m'] = $filters['module']; }
        if ($filters['action']) { $sql .= " AND a.action = :ac"; $p['ac'] = $filters['action']; }
        if ($filters['user'])   { $sql .= " AND a.user_id = :u"; $p['u'] = $filters['user']; }
        if ($filters['from'])   { $sql .= " AND a.created_at >= :f"; $p['f'] = $filters['from'] . ' 00:00:00'; }
        if ($filters['to'])     { $sql .= " AND a.created_at <= :tt"; $p['tt'] = $filters['to'] . ' 23:59:59'; }
        $sql .= " ORDER BY a.created_at DESC LIMIT 200";

        $entries = Database::select($sql, $p);

        $modules = Database::select(
            "SELECT module, COUNT(*) c FROM activity_logs WHERE tenant_id = :t AND module IS NOT NULL GROUP BY module ORDER BY module",
            ['t' => $tid]
        );
        $actions = Database::select(
            "SELECT action, COUNT(*) c FROM activity_logs WHERE tenant_id = :t GROUP BY action ORDER BY action",
            ['t' => $tid]
        );
        $users = Database::select(
            "SELECT u.id, u.name FROM users u WHERE u.tenant_id = :t AND u.deleted_at IS NULL ORDER BY u.name",
            ['t' => $tid]
        );

        $stats = Database::selectOne(
            "SELECT COUNT(*) AS total,
                    SUM(created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)) AS last_24h,
                    SUM(DATE(created_at) = CURDATE()) AS today,
                    COUNT(DISTINCT user_id) AS unique_users
               FROM activity_logs WHERE tenant_id = :t",
            ['t' => $tid]
        ) ?? ['total'=>0,'last_24h'=>0,'today'=>0,'unique_users'=>0];

        $this->renderAdmin('admin/activity/index', [
            'title'       => 'Actividad · Kyros',
            'active'      => 'activity',
            'entries'     => $entries,
            'modules'     => $modules,
            'actions'     => $actions,
            'users'       => $users,
            'filters'     => $filters,
            'stats'       => $stats,
            'breadcrumbs' => [['label'=>'Dashboard','url'=>url('/admin/dashboard')],['label'=>'Actividad']],
        ]);
    }
}
