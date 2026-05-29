<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Plan;
use App\Models\DemoLicense;

class HomeController extends Controller
{
    public function index(Request $request): void
    {
        $this->view('public/landing', [
            'title'       => 'Kyros Rent Car · El sistema operativo de tu rent car',
            'plans'       => Plan::publicPlans(),
            'demoOffers'  => DemoLicense::publicOffers(),
        ], 'marketing');
    }

    public function plans(Request $request): void
    {
        $this->view('public/plans', [
            'title' => 'Planes · Kyros Rent Car',
            'plans' => Plan::publicPlans(),
        ], 'marketing');
    }
}
