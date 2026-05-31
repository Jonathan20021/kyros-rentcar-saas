<?php
/**
 * Route definitions. $router and $request are available from index.php.
 * Handler format: "Sub\\Controller@method" => App\Controllers\Sub\Controller.
 */

use App\Middlewares\AuthMiddleware;
use App\Middlewares\TenantMiddleware;
use App\Middlewares\SuperAdminMiddleware;
use App\Middlewares\PermissionMiddleware;
use App\Middlewares\FeatureMiddleware;

/** @var \App\Core\Router $router */

$auth        = new AuthMiddleware();
$tenant      = new TenantMiddleware();
$superadmin  = new SuperAdminMiddleware();
$perm = fn(string $p) => new PermissionMiddleware($p);
$feat = fn(string $f) => new FeatureMiddleware($f);

// ---------------------------------------------------------------------
// Public marketing + storefront
// ---------------------------------------------------------------------
$router->get('/', 'HomeController@index');
$router->get('/planes', 'HomeController@plans');
$router->get('/producto', 'HomeController@product');
$router->get('/seguridad', 'HomeController@security');
$router->get('/privacidad', 'HomeController@privacy');
$router->get('/terminos', 'HomeController@terms');
$router->get('/contacto', 'HomeController@contact');

// Storefront by slug
$router->get('/r/{slug}', 'PublicSite\\StorefrontController@show');
$router->get('/r/{slug}/vehiculo/{vehicle_slug}', 'PublicSite\\StorefrontController@vehicle');
$router->get('/r/{slug}/reservar/{vehicle_slug}', 'PublicSite\\StorefrontController@reserveForm');
$router->post('/r/{slug}/reservar/{vehicle_slug}', 'PublicSite\\StorefrontController@reserveStore');
$router->get('/r/{slug}/reserva/confirmacion', 'PublicSite\\StorefrontController@confirmation');
$router->get('/r/{slug}/disponibilidad', 'PublicSite\\StorefrontController@availability');
$router->get('/r/{slug}/promo', 'PublicSite\\StorefrontController@promoCheck');

// Public contract share — token = secret, no auth needed.
$router->get('/contrato/{token}', 'PublicSite\\ContractShareController@show');
$router->get('/contrato/{token}/pdf', 'PublicSite\\ContractShareController@pdf');
$router->post('/contrato/{token}/firmar', 'PublicSite\\ContractShareController@sign');

// ---------------------------------------------------------------------
// Authentication
// ---------------------------------------------------------------------
$router->get('/login', 'Auth\\AuthController@showLogin');
$router->post('/login', 'Auth\\AuthController@login');
$router->get('/register', 'Auth\\AuthController@showRegister');
$router->post('/register', 'Auth\\AuthController@register');
$router->post('/demo', 'Auth\\AuthController@demo');
$router->get('/forgot-password', 'Auth\\AuthController@showForgot');
$router->post('/forgot-password', 'Auth\\AuthController@forgot');
$router->get('/reset-password/{token}', 'Auth\\AuthController@showReset');
$router->post('/reset-password', 'Auth\\AuthController@reset');
$router->post('/logout', 'Auth\\AuthController@logout', [$auth]);
$router->get('/logout', 'Auth\\AuthController@logout', [$auth]);

$router->get('/dashboard', 'Auth\\AuthController@home', [$auth]);

// ---------------------------------------------------------------------
// SUPER ADMIN
// ---------------------------------------------------------------------
$router->get('/super-admin', 'SuperAdmin\\DashboardController@index', [$auth, $superadmin]);
$router->get('/super-admin/tenants', 'SuperAdmin\\TenantController@index', [$auth, $superadmin]);
$router->get('/super-admin/tenants/create', 'SuperAdmin\\TenantController@create', [$auth, $superadmin]);
$router->post('/super-admin/tenants', 'SuperAdmin\\TenantController@store', [$auth, $superadmin]);
$router->get('/super-admin/tenants/edit/{id}', 'SuperAdmin\\TenantController@edit', [$auth, $superadmin]);
$router->post('/super-admin/tenants/update/{id}', 'SuperAdmin\\TenantController@update', [$auth, $superadmin]);
$router->post('/super-admin/tenants/suspend/{id}', 'SuperAdmin\\TenantController@suspend', [$auth, $superadmin]);
$router->post('/super-admin/tenants/activate/{id}', 'SuperAdmin\\TenantController@activate', [$auth, $superadmin]);
$router->get('/super-admin/plans', 'SuperAdmin\\PlanController@index', [$auth, $superadmin]);
// Global users — full CRUD (super admins + tenant staff) from the super panel
$router->get ('/super-admin/users',              'SuperAdmin\\UserController@index',   [$auth, $superadmin]);
$router->get ('/super-admin/users/create',       'SuperAdmin\\UserController@create',  [$auth, $superadmin]);
$router->post('/super-admin/users',              'SuperAdmin\\UserController@store',   [$auth, $superadmin]);
$router->get ('/super-admin/users/edit/{id}',    'SuperAdmin\\UserController@edit',    [$auth, $superadmin]);
$router->post('/super-admin/users/update/{id}',  'SuperAdmin\\UserController@update',  [$auth, $superadmin]);
$router->post('/super-admin/users/toggle/{id}',  'SuperAdmin\\UserController@toggle',  [$auth, $superadmin]);
$router->post('/super-admin/users/delete/{id}',  'SuperAdmin\\UserController@destroy', [$auth, $superadmin]);
$router->get('/super-admin/logs', 'SuperAdmin\\LogController@index', [$auth, $superadmin]);
// Approvals — tenant activations + storage requests
$router->get ('/super-admin/approvals',                              'SuperAdmin\\ApprovalsController@index',          [$auth, $superadmin]);
$router->post('/super-admin/approvals/tenant/approve/{id}',          'SuperAdmin\\ApprovalsController@approveTenant',  [$auth, $superadmin]);
$router->post('/super-admin/approvals/tenant/reject/{id}',           'SuperAdmin\\ApprovalsController@rejectTenant',   [$auth, $superadmin]);
$router->post('/super-admin/approvals/storage/approve/{id}',         'SuperAdmin\\ApprovalsController@approveStorage', [$auth, $superadmin]);
$router->post('/super-admin/approvals/storage/reject/{id}',          'SuperAdmin\\ApprovalsController@rejectStorage',  [$auth, $superadmin]);

// Notification routing — recipients per platform event (registrations, demos, logins)
$router->get ('/super-admin/notifications',      'SuperAdmin\\NotificationController@index',  [$auth, $superadmin]);
$router->post('/super-admin/notifications',      'SuperAdmin\\NotificationController@update', [$auth, $superadmin]);
$router->post('/super-admin/notifications/test', 'SuperAdmin\\NotificationController@test',   [$auth, $superadmin]);

$router->get('/super-admin/settings', 'SuperAdmin\\SettingController@index', [$auth, $superadmin]);
$router->post('/super-admin/settings', 'SuperAdmin\\SettingController@update', [$auth, $superadmin]);
$router->post('/super-admin/settings/test', 'SuperAdmin\\SettingController@test', [$auth, $superadmin]);

// ---------------------------------------------------------------------
// RENT CAR ADMIN
// ---------------------------------------------------------------------
$router->get('/admin', 'Admin\\DashboardController@index', [$auth, $tenant]);
$router->get('/admin/dashboard', 'Admin\\DashboardController@index', [$auth, $tenant]);
$router->get('/admin/dashboard/charts', 'Admin\\DashboardController@charts', [$auth, $tenant]);

// Vehicles  — fleet feature (open to all plans)
$router->get('/admin/vehicles', 'Admin\\VehicleController@index', [$auth, $tenant, $perm('vehicles.view')]);
$router->get('/admin/vehicles/show/{id}', 'Admin\\VehicleController@show', [$auth, $tenant, $perm('vehicles.view')]);
$router->get('/admin/vehicles/create', 'Admin\\VehicleController@create', [$auth, $tenant, $perm('vehicles.create')]);
$router->post('/admin/vehicles', 'Admin\\VehicleController@store', [$auth, $tenant, $perm('vehicles.create')]);
$router->get('/admin/vehicles/edit/{id}', 'Admin\\VehicleController@edit', [$auth, $tenant, $perm('vehicles.edit')]);
$router->post('/admin/vehicles/update/{id}', 'Admin\\VehicleController@update', [$auth, $tenant, $perm('vehicles.edit')]);
$router->post('/admin/vehicles/delete/{id}', 'Admin\\VehicleController@destroy', [$auth, $tenant, $perm('vehicles.delete')]);
$router->post('/admin/vehicles/image/main/{id}', 'Admin\\VehicleController@setMainImage', [$auth, $tenant, $perm('vehicles.edit')]);
$router->post('/admin/vehicles/image/delete/{id}', 'Admin\\VehicleController@deleteImage', [$auth, $tenant, $perm('vehicles.edit')]);
$router->post('/admin/vehicles/status/{id}', 'Admin\\VehicleController@changeStatus', [$auth, $tenant, $perm('vehicles.change_status')]);

// Customers
$router->get('/admin/customers', 'Admin\\CustomerController@index', [$auth, $tenant, $perm('customers.view')]);
$router->get('/admin/customers/show/{id}', 'Admin\\CustomerController@show', [$auth, $tenant, $perm('customers.view')]);
$router->get('/admin/customers/create', 'Admin\\CustomerController@create', [$auth, $tenant, $perm('customers.create')]);
$router->post('/admin/customers', 'Admin\\CustomerController@store', [$auth, $tenant, $perm('customers.create')]);
$router->get('/admin/customers/edit/{id}', 'Admin\\CustomerController@edit', [$auth, $tenant, $perm('customers.edit')]);
$router->post('/admin/customers/update/{id}', 'Admin\\CustomerController@update', [$auth, $tenant, $perm('customers.edit')]);
$router->post('/admin/customers/delete/{id}', 'Admin\\CustomerController@destroy', [$auth, $tenant, $perm('customers.delete')]);
$router->post('/admin/customers/bulk-delete',  'Admin\\CustomerController@bulkDestroy', [$auth, $tenant, $perm('customers.delete')]);

// Reservations
$router->get('/admin/reservations', 'Admin\\ReservationController@index', [$auth, $tenant, $perm('reservations.view')]);
$router->get('/admin/reservations/calendar', 'Admin\\ReservationController@calendar', [$auth, $tenant, $perm('reservations.view')]);
$router->get('/admin/reservations/events', 'Admin\\ReservationController@events', [$auth, $tenant, $perm('reservations.view')]);
$router->get('/admin/reservations/create', 'Admin\\ReservationController@create', [$auth, $tenant, $perm('reservations.create')]);
$router->post('/admin/reservations', 'Admin\\ReservationController@store', [$auth, $tenant, $perm('reservations.create')]);
$router->get('/admin/reservations/availability', 'Admin\\ReservationController@availability', [$auth, $tenant, $perm('reservations.view')]);
$router->get('/admin/reservations/show/{id}', 'Admin\\ReservationController@show', [$auth, $tenant, $perm('reservations.view')]);
$router->post('/admin/reservations/convert/{id}', 'Admin\\ReservationController@convert', [$auth, $tenant, $feat('contracts'), $perm('contracts.create')]);
$router->post('/admin/reservations/status/{id}', 'Admin\\ReservationController@changeStatus', [$auth, $tenant, $perm('reservations.change_status')]);
$router->post('/admin/reservations/assign-customer/{id}', 'Admin\\ReservationController@assignCustomer', [$auth, $tenant, $perm('reservations.edit')]);
$router->post('/admin/reservations/cancel/{id}', 'Admin\\ReservationController@cancel', [$auth, $tenant, $perm('reservations.cancel')]);

// Contracts — Business+
$router->get('/admin/contracts', 'Admin\\ContractController@index', [$auth, $tenant, $feat('contracts'), $perm('contracts.view')]);
$router->get('/admin/contracts/show/{id}', 'Admin\\ContractController@show', [$auth, $tenant, $feat('contracts'), $perm('contracts.view')]);
$router->get('/admin/contracts/pdf/{id}', 'Admin\\ContractController@pdf', [$auth, $tenant, $feat('contracts'), $perm('contracts.view')]);
$router->get('/admin/contracts/close/{id}', 'Admin\\ContractController@closeForm', [$auth, $tenant, $feat('contracts'), $perm('contracts.edit')]);
$router->post('/admin/contracts/close/{id}', 'Admin\\ContractController@close', [$auth, $tenant, $feat('contracts'), $perm('contracts.edit')]);
$router->post('/admin/contracts/sign/{id}', 'Admin\\ContractController@sign', [$auth, $tenant, $feat('contracts'), $perm('contracts.edit')]);
$router->post('/admin/contracts/share/{id}', 'Admin\\ContractController@share', [$auth, $tenant, $feat('contracts'), $perm('contracts.edit')]);
$router->post('/admin/contracts/share-revoke/{id}', 'Admin\\ContractController@revokeShare', [$auth, $tenant, $feat('contracts'), $perm('contracts.edit')]);

// Payments — Business+
$router->get('/admin/payments', 'Admin\\PaymentController@index', [$auth, $tenant, $feat('payments'), $perm('payments.view')]);
$router->get('/admin/payments/create', 'Admin\\PaymentController@create', [$auth, $tenant, $feat('payments'), $perm('payments.create')]);
$router->post('/admin/payments', 'Admin\\PaymentController@store', [$auth, $tenant, $feat('payments'), $perm('payments.create')]);
$router->get('/admin/payments/receipt/{id}', 'Admin\\PaymentController@receipt', [$auth, $tenant, $feat('payments'), $perm('payments.view')]);
$router->post('/admin/payments/void/{id}', 'Admin\\PaymentController@void', [$auth, $tenant, $feat('payments'), $perm('payments.edit')]);

// Invoices — Business+
$router->get('/admin/invoices', 'Admin\\InvoiceController@index', [$auth, $tenant, $feat('invoices'), $perm('invoices.view')]);
$router->get('/admin/invoices/create', 'Admin\\InvoiceController@create', [$auth, $tenant, $feat('invoices'), $perm('invoices.create')]);
$router->post('/admin/invoices', 'Admin\\InvoiceController@store', [$auth, $tenant, $feat('invoices'), $perm('invoices.create')]);
$router->get('/admin/invoices/show/{id}', 'Admin\\InvoiceController@show', [$auth, $tenant, $feat('invoices'), $perm('invoices.view')]);
$router->get('/admin/invoices/pdf/{id}', 'Admin\\InvoiceController@pdf', [$auth, $tenant, $feat('invoices'), $perm('invoices.view')]);
$router->post('/admin/invoices/status/{id}', 'Admin\\InvoiceController@status', [$auth, $tenant, $feat('invoices'), $perm('invoices.edit')]);

// Maintenance — Business+
$router->get('/admin/maintenance', 'Admin\\MaintenanceController@index', [$auth, $tenant, $feat('maintenance'), $perm('maintenance.view')]);
$router->get('/admin/maintenance/create', 'Admin\\MaintenanceController@create', [$auth, $tenant, $feat('maintenance'), $perm('maintenance.create')]);
$router->post('/admin/maintenance', 'Admin\\MaintenanceController@store', [$auth, $tenant, $feat('maintenance'), $perm('maintenance.create')]);
$router->get('/admin/maintenance/edit/{id}', 'Admin\\MaintenanceController@edit', [$auth, $tenant, $feat('maintenance'), $perm('maintenance.edit')]);
$router->post('/admin/maintenance/update/{id}', 'Admin\\MaintenanceController@update', [$auth, $tenant, $feat('maintenance'), $perm('maintenance.edit')]);
$router->post('/admin/maintenance/complete/{id}', 'Admin\\MaintenanceController@complete', [$auth, $tenant, $feat('maintenance'), $perm('maintenance.edit')]);
$router->post('/admin/maintenance/delete/{id}', 'Admin\\MaintenanceController@destroy', [$auth, $tenant, $feat('maintenance'), $perm('maintenance.delete')]);

// Notifications
$router->get('/admin/notifications', 'Admin\\NotificationController@index', [$auth, $tenant]);
$router->post('/admin/notifications/read/{id}', 'Admin\\NotificationController@read', [$auth, $tenant]);
$router->post('/admin/notifications/read-all', 'Admin\\NotificationController@readAll', [$auth, $tenant]);

// Document expirations — Business+
$router->get('/admin/documents', 'Admin\\DocumentController@index', [$auth, $tenant, $feat('documents'), $perm('vehicles.view')]);

// Incidents — Business+
$router->get('/admin/incidents', 'Admin\\IncidentController@index', [$auth, $tenant, $feat('incidents'), $perm('incidents.view')]);
$router->get('/admin/incidents/show/{id}', 'Admin\\IncidentController@show', [$auth, $tenant, $feat('incidents'), $perm('incidents.view')]);
$router->get('/admin/incidents/create', 'Admin\\IncidentController@create', [$auth, $tenant, $feat('incidents'), $perm('incidents.create')]);
$router->post('/admin/incidents', 'Admin\\IncidentController@store', [$auth, $tenant, $feat('incidents'), $perm('incidents.create')]);
$router->post('/admin/incidents/status/{id}', 'Admin\\IncidentController@changeStatus', [$auth, $tenant, $feat('incidents'), $perm('incidents.edit')]);

// Reports — Business+
$router->get('/admin/reports', 'Admin\\ReportController@index', [$auth, $tenant, $feat('reports'), $perm('reports.view')]);
$router->get('/admin/reports/pnl', 'Admin\\ReportController@pnl', [$auth, $tenant, $feat('reports'), $perm('reports.view')]);
$router->get('/admin/reports/export/{type}', 'Admin\\ReportController@export', [$auth, $tenant, $feat('reports'), $perm('reports.export')]);

// Team users (tenant staff)
$router->get('/admin/users', 'Admin\\UserController@index', [$auth, $tenant, $perm('users.view')]);
$router->get('/admin/users/create', 'Admin\\UserController@create', [$auth, $tenant, $perm('users.create')]);
$router->post('/admin/users', 'Admin\\UserController@store', [$auth, $tenant, $perm('users.create')]);
$router->get('/admin/users/edit/{id}', 'Admin\\UserController@edit', [$auth, $tenant, $perm('users.edit')]);
$router->post('/admin/users/update/{id}', 'Admin\\UserController@update', [$auth, $tenant, $perm('users.edit')]);
$router->post('/admin/users/toggle/{id}', 'Admin\\UserController@toggle', [$auth, $tenant, $perm('users.edit')]);

$router->get('/admin/settings', 'Admin\\SettingController@index', [$auth, $tenant, $perm('settings.view')]);
$router->post('/admin/settings', 'Admin\\SettingController@update', [$auth, $tenant, $perm('settings.edit')]);

// NCF sequences (RD DGII compliance)
$router->get ('/admin/ncf',                    'Admin\\NcfController@index',       [$auth, $tenant, $perm('settings.view')]);
$router->post('/admin/ncf',                    'Admin\\NcfController@store',       [$auth, $tenant, $perm('settings.edit')]);
$router->post('/admin/ncf/disable/{id}',       'Admin\\NcfController@disable',     [$auth, $tenant, $perm('settings.edit')]);

// Storage quota + extra-storage requests
$router->get ('/admin/storage',                'Admin\\StorageController@index',   [$auth, $tenant, $perm('settings.view')]);
$router->post('/admin/storage/refresh',        'Admin\\StorageController@refresh', [$auth, $tenant, $perm('settings.view')]);
$router->post('/admin/storage/request',        'Admin\\StorageController@request', [$auth, $tenant, $perm('settings.edit')]);
$router->post('/admin/storage/cancel/{id}',    'Admin\\StorageController@cancel',  [$auth, $tenant, $perm('settings.edit')]);

// Vehicle categories
$router->get('/admin/categories', 'Admin\\CategoryController@index', [$auth, $tenant, $perm('catalog.view')]);
$router->post('/admin/categories', 'Admin\\CategoryController@store', [$auth, $tenant, $perm('catalog.manage')]);
$router->post('/admin/categories/update/{id}', 'Admin\\CategoryController@update', [$auth, $tenant, $perm('catalog.manage')]);
$router->post('/admin/categories/delete/{id}', 'Admin\\CategoryController@destroy', [$auth, $tenant, $perm('catalog.manage')]);

// Extras (catalog)
$router->get('/admin/extras', 'Admin\\ExtraController@index', [$auth, $tenant, $perm('catalog.view')]);
$router->post('/admin/extras', 'Admin\\ExtraController@store', [$auth, $tenant, $perm('catalog.manage')]);
$router->post('/admin/extras/update/{id}', 'Admin\\ExtraController@update', [$auth, $tenant, $perm('catalog.manage')]);
$router->post('/admin/extras/delete/{id}', 'Admin\\ExtraController@destroy', [$auth, $tenant, $perm('catalog.manage')]);

// Cash closing — Business+
$router->get('/admin/cashbox', 'Admin\\CashClosingController@index', [$auth, $tenant, $feat('cashbox'), $perm('cashbox.view')]);
$router->get('/admin/cashbox/create', 'Admin\\CashClosingController@create', [$auth, $tenant, $feat('cashbox'), $perm('cashbox.manage')]);
$router->post('/admin/cashbox', 'Admin\\CashClosingController@store', [$auth, $tenant, $feat('cashbox'), $perm('cashbox.manage')]);
$router->get('/admin/cashbox/show/{id}', 'Admin\\CashClosingController@show', [$auth, $tenant, $feat('cashbox'), $perm('cashbox.view')]);

// Expenses — Business+
$router->get('/admin/expenses', 'Admin\\ExpenseController@index', [$auth, $tenant, $feat('expenses'), $perm('expenses.view')]);
$router->get('/admin/expenses/create', 'Admin\\ExpenseController@create', [$auth, $tenant, $feat('expenses'), $perm('expenses.create')]);
$router->post('/admin/expenses', 'Admin\\ExpenseController@store', [$auth, $tenant, $feat('expenses'), $perm('expenses.create')]);
$router->get('/admin/expenses/edit/{id}', 'Admin\\ExpenseController@edit', [$auth, $tenant, $feat('expenses'), $perm('expenses.edit')]);
$router->post('/admin/expenses/update/{id}', 'Admin\\ExpenseController@update', [$auth, $tenant, $feat('expenses'), $perm('expenses.edit')]);
$router->post('/admin/expenses/delete/{id}', 'Admin\\ExpenseController@destroy', [$auth, $tenant, $feat('expenses'), $perm('expenses.delete')]);

// Locations / branches — Business+ (multi-sucursal)
$router->get('/admin/locations', 'Admin\\LocationController@index', [$auth, $tenant, $feat('multi_location'), $perm('locations.view')]);
$router->get('/admin/locations/create', 'Admin\\LocationController@create', [$auth, $tenant, $feat('multi_location'), $perm('locations.create')]);
$router->post('/admin/locations', 'Admin\\LocationController@store', [$auth, $tenant, $feat('multi_location'), $perm('locations.create')]);
$router->get('/admin/locations/edit/{id}', 'Admin\\LocationController@edit', [$auth, $tenant, $feat('multi_location'), $perm('locations.edit')]);
$router->post('/admin/locations/update/{id}', 'Admin\\LocationController@update', [$auth, $tenant, $feat('multi_location'), $perm('locations.edit')]);
$router->post('/admin/locations/delete/{id}', 'Admin\\LocationController@destroy', [$auth, $tenant, $feat('multi_location'), $perm('locations.delete')]);

// Email templates — Business+
$router->get('/admin/emails', 'Admin\\EmailTemplateController@index', [$auth, $tenant, $feat('email_templates'), $perm('settings.view')]);
$router->get('/admin/emails/edit/{code}', 'Admin\\EmailTemplateController@edit', [$auth, $tenant, $feat('email_templates'), $perm('settings.view')]);
$router->post('/admin/emails/update/{code}', 'Admin\\EmailTemplateController@update', [$auth, $tenant, $feat('email_templates'), $perm('settings.edit')]);
$router->post('/admin/emails/reset/{code}', 'Admin\\EmailTemplateController@reset', [$auth, $tenant, $feat('email_templates'), $perm('settings.edit')]);
$router->post('/admin/emails/test/{code}', 'Admin\\EmailTemplateController@test', [$auth, $tenant, $feat('email_templates'), $perm('settings.edit')]);

// API — Premium only
$router->get('/admin/api', 'Admin\\ApiController@index', [$auth, $tenant, $feat('api'), $perm('api.view')]);
$router->post('/admin/api', 'Admin\\ApiController@store', [$auth, $tenant, $feat('api'), $perm('api.manage')]);
$router->post('/admin/api/revoke/{id}', 'Admin\\ApiController@revoke', [$auth, $tenant, $feat('api'), $perm('api.manage')]);

// Promo codes — Business+
$router->get('/admin/promos', 'Admin\\PromoCodeController@index', [$auth, $tenant, $feat('promos'), $perm('promos.view')]);
$router->get('/admin/promos/create', 'Admin\\PromoCodeController@create', [$auth, $tenant, $feat('promos'), $perm('promos.manage')]);
$router->post('/admin/promos', 'Admin\\PromoCodeController@store', [$auth, $tenant, $feat('promos'), $perm('promos.manage')]);
$router->get('/admin/promos/edit/{id}', 'Admin\\PromoCodeController@edit', [$auth, $tenant, $feat('promos'), $perm('promos.manage')]);
$router->post('/admin/promos/update/{id}', 'Admin\\PromoCodeController@update', [$auth, $tenant, $feat('promos'), $perm('promos.manage')]);
$router->post('/admin/promos/delete/{id}', 'Admin\\PromoCodeController@destroy', [$auth, $tenant, $feat('promos'), $perm('promos.manage')]);

// Drivers — Business+
$router->get('/admin/drivers', 'Admin\\DriverController@index', [$auth, $tenant, $feat('drivers'), $perm('drivers.view')]);
$router->get('/admin/drivers/create', 'Admin\\DriverController@create', [$auth, $tenant, $feat('drivers'), $perm('drivers.create')]);
$router->post('/admin/drivers', 'Admin\\DriverController@store', [$auth, $tenant, $feat('drivers'), $perm('drivers.create')]);
$router->get('/admin/drivers/show/{id}', 'Admin\\DriverController@show', [$auth, $tenant, $feat('drivers'), $perm('drivers.view')]);
$router->get('/admin/drivers/edit/{id}', 'Admin\\DriverController@edit', [$auth, $tenant, $feat('drivers'), $perm('drivers.edit')]);
$router->post('/admin/drivers/update/{id}', 'Admin\\DriverController@update', [$auth, $tenant, $feat('drivers'), $perm('drivers.edit')]);
$router->post('/admin/drivers/delete/{id}', 'Admin\\DriverController@destroy', [$auth, $tenant, $feat('drivers'), $perm('drivers.delete')]);

// Activity log
$router->get('/admin/activity', 'Admin\\ActivityController@index', [$auth, $tenant, $perm('settings.view')]);

// Global search (palette ⌘K)
$router->get('/admin/search', 'Admin\\SearchController@search', [$auth, $tenant]);

// Plan / upgrade page
$router->get('/admin/upgrade', 'Admin\\UpgradeController@index', [$auth, $tenant]);
