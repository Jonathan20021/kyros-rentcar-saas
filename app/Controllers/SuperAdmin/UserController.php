<?php
namespace App\Controllers\SuperAdmin;

use App\Core\Controller;
use App\Core\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index(Request $request): void
    {
        $this->view('superadmin/users', [
            'title'  => 'Usuarios · Super Admin',
            'panel'  => 'super',
            'active' => 'users',
            'users'  => User::allSystem(),
        ], 'app');
    }
}
