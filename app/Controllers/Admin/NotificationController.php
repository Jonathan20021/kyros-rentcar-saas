<?php
namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Models\Notification;

class NotificationController extends AdminController
{
    public function index(Request $request): void
    {
        $tid = $this->tenantId();
        $onlyUnread = $request->str('filter') === 'unread';
        $this->renderAdmin('admin/notifications/index', [
            'title'   => 'Notificaciones · Kyros Rent Car',
            'active'  => '',
            'items'   => Notification::allForTenant($tid, $onlyUnread),
            'unread'  => Notification::unreadCount($tid),
            'onlyUnread' => $onlyUnread,
            'breadcrumbs' => [['label'=>'Dashboard','url'=>url('/admin/dashboard')],['label'=>'Notificaciones']],
        ]);
    }

    public function read(Request $request, string $id): void
    {
        $tid = $this->tenantId();
        $n = Notification::find($tid, (int) $id);
        if ($n) { Notification::markRead($tid, (int) $id); }
        // Follow the notification's action when present.
        if ($n && !empty($n['action_url'])) { $this->redirect($n['action_url']); }
        $this->back();
    }

    public function readAll(Request $request): void
    {
        Notification::markAllRead($this->tenantId());
        Session::flash('success', 'Todas las notificaciones marcadas como leídas.');
        $this->back();
    }
}
