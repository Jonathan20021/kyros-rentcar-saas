<?php
namespace App\Controllers\SuperAdmin;

use App\Core\Controller;
use App\Core\Request;
use App\Models\ActivityLog;

class LogController extends Controller
{
    public function index(Request $request): void
    {
        $this->view('superadmin/logs', [
            'title'  => 'Logs · Super Admin',
            'panel'  => 'super',
            'active' => 'logs',
            'logs'   => ActivityLog::recent(100),
        ], 'app');
    }
}
