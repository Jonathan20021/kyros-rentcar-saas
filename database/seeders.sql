-- =====================================================================
-- KYROS RENT CAR - Core Seeders (plans, roles, permissions, super admin)
-- Run AFTER schema.sql. Idempotent-ish: truncates the seeded base tables.
-- =====================================================================
USE `kyros_rentcar`;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- PLANS
-- ---------------------------------------------------------------------
TRUNCATE TABLE `plans`;
INSERT INTO `plans` (`id`,`name`,`slug`,`price_monthly`,`price_yearly`,`max_vehicles`,`max_users`,`storage_mb`,`features`,`is_public`,`status`) VALUES
(1,'Starter','starter', 990.00,  9900.00, 10,  2,   500,
  JSON_ARRAY('Pagina publica con slug','Reservas basicas','Gestion de flotilla','500 MB de almacenamiento','Soporte por email'),1,'active'),
(2,'Business','business',2490.00, 24900.00, 50, 10, 5000,
  JSON_ARRAY('Todo lo de Starter','Contratos PDF','Pagos','Mantenimiento','Reportes','Multi-sucursal','5 GB de almacenamiento'),1,'active'),
(3,'Premium','premium',4990.00, 49900.00, -1, -1, 25000,
  JSON_ARRAY('Todo lo de Business','Vehiculos ilimitados','Usuarios ilimitados','API REST','Dominio personalizado','WhatsApp','Automatizaciones','Reportes avanzados','25 GB de almacenamiento'),1,'active');

-- ---------------------------------------------------------------------
-- ROLES
-- ---------------------------------------------------------------------
TRUNCATE TABLE `roles`;
INSERT INTO `roles` (`id`,`name`,`slug`,`scope`,`description`) VALUES
(1,'Super Admin Kyros','super-admin','system','Control total de la plataforma SaaS'),
(2,'Dueno de Rent Car','owner','tenant','Propietario de la empresa, acceso total al tenant'),
(3,'Administrador','admin','tenant','Administra operaciones de la rent car'),
(4,'Agente de Reservas','agent','tenant','Gestiona reservas y clientes'),
(5,'Encargado de Flotilla','fleet','tenant','Gestiona vehiculos y mantenimiento'),
(6,'Contabilidad','accounting','tenant','Gestiona pagos, facturas y reportes'),
(7,'Chofer / Delivery','driver','tenant','Entregas y devoluciones'),
(8,'Cliente Final','customer','tenant','Cliente del portal publico');

-- ---------------------------------------------------------------------
-- PERMISSIONS  (module.action)
-- ---------------------------------------------------------------------
TRUNCATE TABLE `permissions`;
INSERT INTO `permissions` (`module`,`action`,`slug`) VALUES
('dashboard','view','dashboard.view'),
('vehicles','view','vehicles.view'),('vehicles','create','vehicles.create'),('vehicles','edit','vehicles.edit'),('vehicles','delete','vehicles.delete'),('vehicles','export','vehicles.export'),('vehicles','change_status','vehicles.change_status'),
('customers','view','customers.view'),('customers','create','customers.create'),('customers','edit','customers.edit'),('customers','delete','customers.delete'),('customers','export','customers.export'),
('reservations','view','reservations.view'),('reservations','create','reservations.create'),('reservations','edit','reservations.edit'),('reservations','delete','reservations.delete'),('reservations','approve','reservations.approve'),('reservations','cancel','reservations.cancel'),('reservations','change_status','reservations.change_status'),
('contracts','view','contracts.view'),('contracts','create','contracts.create'),('contracts','edit','contracts.edit'),('contracts','delete','contracts.delete'),('contracts','export','contracts.export'),
('payments','view','payments.view'),('payments','create','payments.create'),('payments','edit','payments.edit'),('payments','delete','payments.delete'),('payments','export','payments.export'),
('invoices','view','invoices.view'),('invoices','create','invoices.create'),('invoices','edit','invoices.edit'),('invoices','export','invoices.export'),
('maintenance','view','maintenance.view'),('maintenance','create','maintenance.create'),('maintenance','edit','maintenance.edit'),('maintenance','delete','maintenance.delete'),
('incidents','view','incidents.view'),('incidents','create','incidents.create'),('incidents','edit','incidents.edit'),
('reports','view','reports.view'),('reports','export','reports.export'),
('settings','view','settings.view'),('settings','edit','settings.edit'),
('locations','view','locations.view'),('locations','create','locations.create'),('locations','edit','locations.edit'),('locations','delete','locations.delete'),
('catalog','view','catalog.view'),('catalog','manage','catalog.manage'),
('expenses','view','expenses.view'),('expenses','create','expenses.create'),('expenses','edit','expenses.edit'),('expenses','delete','expenses.delete'),
('cashbox','view','cashbox.view'),('cashbox','manage','cashbox.manage'),
('api','view','api.view'),('api','manage','api.manage'),
('users','view','users.view'),('users','create','users.create'),('users','edit','users.edit'),('users','delete','users.delete'),
('promos','view','promos.view'),('promos','manage','promos.manage'),
('drivers','view','drivers.view'),('drivers','create','drivers.create'),('drivers','edit','drivers.edit'),('drivers','delete','drivers.delete');

-- ---------------------------------------------------------------------
-- ROLE_PERMISSIONS
--   owner & admin -> all tenant permissions
--   agent -> dashboard, vehicles.view, customers*, reservations*, contracts.view
--   fleet -> dashboard, vehicles*, maintenance*, incidents*
--   accounting -> dashboard, payments*, invoices*, reports*, contracts.view
--   driver -> dashboard, reservations.view, contracts.view
-- (super-admin bypasses permission checks in code)
-- ---------------------------------------------------------------------
TRUNCATE TABLE `role_permissions`;
-- owner (2) & admin (3): everything
INSERT INTO `role_permissions` (`role_id`,`permission_id`)
SELECT 2, id FROM `permissions`;
INSERT INTO `role_permissions` (`role_id`,`permission_id`)
SELECT 3, id FROM `permissions`;
-- agent (4)
INSERT INTO `role_permissions` (`role_id`,`permission_id`)
SELECT 4, id FROM `permissions`
WHERE `slug` IN ('dashboard.view','vehicles.view','customers.view','customers.create','customers.edit',
'reservations.view','reservations.create','reservations.edit','reservations.cancel','reservations.change_status','contracts.view');
-- fleet (5)
INSERT INTO `role_permissions` (`role_id`,`permission_id`)
SELECT 5, id FROM `permissions`
WHERE `slug` IN ('dashboard.view','vehicles.view','vehicles.create','vehicles.edit','vehicles.delete','vehicles.change_status',
'maintenance.view','maintenance.create','maintenance.edit','maintenance.delete','incidents.view','incidents.create','incidents.edit');
-- accounting (6)
INSERT INTO `role_permissions` (`role_id`,`permission_id`)
SELECT 6, id FROM `permissions`
WHERE `slug` IN ('dashboard.view','payments.view','payments.create','payments.edit','payments.export',
'invoices.view','invoices.create','invoices.edit','invoices.export','reports.view','reports.export','contracts.view');
-- driver (7)
INSERT INTO `role_permissions` (`role_id`,`permission_id`)
SELECT 7, id FROM `permissions`
WHERE `slug` IN ('dashboard.view','reservations.view','contracts.view');

-- ---------------------------------------------------------------------
-- SUPER ADMIN USER  (tenant_id NULL)
--   email: admin@kyrosrd.com  /  password: Admin123*
-- ---------------------------------------------------------------------
DELETE FROM `users` WHERE `email` = 'admin@kyrosrd.com' AND `tenant_id` IS NULL;
INSERT INTO `users` (`tenant_id`,`role_id`,`name`,`email`,`password`,`status`,`email_verified_at`) VALUES
(NULL, 1, 'Kyros Super Admin', 'admin@kyrosrd.com',
 '$2y$10$cDR1fa8onuQwoQ4Ixd1vMerykz2J.HHe2H30kCuLbFJqPq9XzsOTu', 'active', NOW());

SET FOREIGN_KEY_CHECKS = 1;
