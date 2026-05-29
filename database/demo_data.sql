-- =====================================================================
-- KYROS RENT CAR - Demo Data
-- Run AFTER schema.sql and seeders.sql.
-- Demo tenant "Kyros Rent Car" (slug: kyros-rent-car)
--   Owner login: owner@demo.com / Demo123*
-- =====================================================================
USE `kyros_rentcar`;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- TENANT
-- ---------------------------------------------------------------------
DELETE FROM `tenants` WHERE `slug` = 'kyros-rent-car';
INSERT INTO `tenants`
  (`id`,`name`,`slug`,`legal_name`,`rnc`,`phone`,`whatsapp`,`email`,`address`,`description`,`primary_color`,`secondary_color`,`currency`,`tax_rate`,`plan_id`,`status`,`trial_ends_at`)
VALUES
  (1,'Kyros Rent Car','kyros-rent-car','Kyros Mobility SRL','1-31-12345-6','+1 809 555 0100','18095550100','contacto@kyrosrentcar.com',
   'Av. Winston Churchill 95, Santo Domingo, RD',
   'Renta de vehiculos premium en Republica Dominicana. Flotilla moderna, precios transparentes y atencion 24/7.',
   '#F23645','#1C2433','DOP',18.00,2,'active', DATE_ADD(CURDATE(), INTERVAL 30 DAY));

-- subscription
DELETE FROM `tenant_subscriptions` WHERE `tenant_id` = 1;
INSERT INTO `tenant_subscriptions` (`tenant_id`,`plan_id`,`billing_cycle`,`amount`,`starts_at`,`status`)
VALUES (1,2,'monthly',2490.00,CURDATE(),'active');

-- ---------------------------------------------------------------------
-- USERS (tenant staff)  password: Demo123*
-- ---------------------------------------------------------------------
DELETE FROM `users` WHERE `tenant_id` = 1;
INSERT INTO `users` (`tenant_id`,`role_id`,`name`,`email`,`password`,`phone`,`status`,`email_verified_at`) VALUES
(1,2,'Carlos Owner','owner@demo.com',   '$2y$10$sBxojLBJ2UeCJEbU51VAUOpgNss4ymgtDr.bMo6ODF8rmtde9UQaK','+1 809 555 0101','active',NOW()),
(1,3,'Ana Administradora','admin@demo.com','$2y$10$sBxojLBJ2UeCJEbU51VAUOpgNss4ymgtDr.bMo6ODF8rmtde9UQaK','+1 809 555 0102','active',NOW()),
(1,4,'Luis Agente','agente@demo.com',    '$2y$10$sBxojLBJ2UeCJEbU51VAUOpgNss4ymgtDr.bMo6ODF8rmtde9UQaK','+1 809 555 0103','active',NOW());

-- ---------------------------------------------------------------------
-- LOCATIONS
-- ---------------------------------------------------------------------
DELETE FROM `locations` WHERE `tenant_id` = 1;
INSERT INTO `locations` (`id`,`tenant_id`,`name`,`address`,`phone`,`manager_name`,`status`) VALUES
(1,1,'Sucursal Principal','Av. Winston Churchill 95, Santo Domingo','+1 809 555 0100','Carlos Owner','active'),
(2,1,'Aeropuerto Las Americas','Terminal A, AILA','+1 809 555 0110','Ana Administradora','active');

-- ---------------------------------------------------------------------
-- VEHICLE CATEGORIES
-- ---------------------------------------------------------------------
DELETE FROM `vehicle_categories` WHERE `tenant_id` = 1;
INSERT INTO `vehicle_categories` (`id`,`tenant_id`,`name`,`slug`,`icon`,`status`) VALUES
(1,1,'Economico','economico','car','active'),
(2,1,'Compacto','compacto','car','active'),
(3,1,'Sedan','sedan','car','active'),
(4,1,'SUV','suv','truck','active'),
(5,1,'Jeepeta','jeepeta','truck','active'),
(6,1,'Van','van','bus','active'),
(7,1,'Pickup','pickup','truck','active'),
(8,1,'Lujo','lujo','sparkles','active'),
(9,1,'Deportivo','deportivo','zap','active'),
(10,1,'Electrico','electrico','battery-charging','active');

-- ---------------------------------------------------------------------
-- EXTRAS
-- ---------------------------------------------------------------------
DELETE FROM `extras` WHERE `tenant_id` = 1;
INSERT INTO `extras` (`tenant_id`,`name`,`description`,`price`,`charge_type`,`status`) VALUES
(1,'Silla de bebe','Silla de seguridad para ninos',300.00,'per_day','active'),
(1,'GPS','Navegador GPS',200.00,'per_day','active'),
(1,'WiFi portatil','Hotspot 4G ilimitado',250.00,'per_day','active'),
(1,'Seguro adicional','Cobertura full damage',500.00,'per_day','active'),
(1,'Segundo conductor','Conductor adicional autorizado',400.00,'per_reservation','active'),
(1,'Entrega a domicilio','Entrega del vehiculo en tu direccion',800.00,'one_time','active'),
(1,'Recogida en aeropuerto','Pickup en AILA',1000.00,'one_time','active');

-- ---------------------------------------------------------------------
-- VEHICLES (10)
-- ---------------------------------------------------------------------
DELETE FROM `vehicles` WHERE `tenant_id` = 1;
INSERT INTO `vehicles`
 (`id`,`tenant_id`,`brand`,`model`,`version`,`year`,`plate_number`,`vin`,`color`,`category_id`,`transmission`,`fuel_type`,`mileage`,`passengers`,`doors`,`luggage_capacity`,`daily_price`,`weekly_price`,`monthly_price`,`deposit_amount`,`insurance_price`,`status`,`location_id`,`description`,`features`,`main_image`,`slug`,`insurance_expires`,`marbete_expires`,`is_featured`,`is_public`)
VALUES
(1,1,'Toyota','Corolla','LE',2023,'A123456','VIN0000000000001','Blanco',3,'automatic','gasoline',18500,5,4,2,2200.00,13000.00,48000.00,10000.00,500.00,'available',1,'Sedan confiable y economico, ideal para ciudad.',JSON_ARRAY('Bluetooth','Camara reversa','A/C','Pantalla tactil'),NULL,'toyota-corolla-le-2023',DATE_ADD(CURDATE(),INTERVAL 60 DAY),DATE_ADD(CURDATE(),INTERVAL 200 DAY),1,1),
(2,1,'Honda','Civic','Sport',2022,'B234567','VIN0000000000002','Gris',3,'automatic','gasoline',32000,5,4,2,2400.00,14000.00,52000.00,10000.00,500.00,'available',1,'Civic Sport con excelente rendimiento.',JSON_ARRAY('Bluetooth','CarPlay','A/C','Rines deportivos'),NULL,'honda-civic-sport-2022',DATE_ADD(CURDATE(),INTERVAL 90 DAY),DATE_ADD(CURDATE(),INTERVAL 180 DAY),1,1),
(3,1,'Hyundai','Tucson','GLS',2023,'C345678','VIN0000000000003','Negro',4,'automatic','gasoline',15000,5,5,3,3200.00,19000.00,70000.00,15000.00,700.00,'rented',1,'SUV espaciosa y comoda para viajes familiares.',JSON_ARRAY('Bluetooth','Camara 360','Sunroof','A/C dual'),NULL,'hyundai-tucson-gls-2023',DATE_ADD(CURDATE(),INTERVAL 45 DAY),DATE_ADD(CURDATE(),INTERVAL 150 DAY),1,1),
(4,1,'Kia','Sportage','EX',2022,'D456789','VIN0000000000004','Rojo',4,'automatic','gasoline',28000,5,5,3,3000.00,18000.00,66000.00,15000.00,700.00,'available',1,'SUV moderna con tecnologia avanzada.',JSON_ARRAY('Bluetooth','CarPlay','Camara reversa','A/C'),NULL,'kia-sportage-ex-2022',DATE_ADD(CURDATE(),INTERVAL 30 DAY),DATE_ADD(CURDATE(),INTERVAL 120 DAY),0,1),
(5,1,'Toyota','Hilux','SRV 4x4',2023,'E567890','VIN0000000000005','Plata',7,'automatic','diesel',22000,5,4,4,4000.00,24000.00,88000.00,20000.00,900.00,'maintenance',1,'Pickup 4x4 robusta para cualquier terreno.',JSON_ARRAY('4x4','Bluetooth','Cama protegida','A/C'),NULL,'toyota-hilux-srv-4x4-2023',DATE_ADD(CURDATE(),INTERVAL 10 DAY),DATE_ADD(CURDATE(),INTERVAL 90 DAY),1,1),
(6,1,'Nissan','Versa','Sense',2021,'F678901','VIN0000000000006','Azul',1,'automatic','gasoline',45000,5,4,2,1800.00,10500.00,39000.00,8000.00,400.00,'available',1,'Economico y eficiente en combustible.',JSON_ARRAY('Bluetooth','A/C','USB'),NULL,'nissan-versa-sense-2021',DATE_ADD(CURDATE(),INTERVAL 5 DAY),DATE_ADD(CURDATE(),INTERVAL 75 DAY),0,1),
(7,1,'Mercedes-Benz','C300','AMG Line',2023,'G789012','VIN0000000000007','Negro',8,'automatic','gasoline',12000,5,4,2,7500.00,45000.00,165000.00,40000.00,1500.00,'available',1,'Lujo y rendimiento en un solo vehiculo.',JSON_ARRAY('Cuero','Sunroof','Burmester audio','Asientos ventilados'),NULL,'mercedes-benz-c300-amg-line-2023',DATE_ADD(CURDATE(),INTERVAL 120 DAY),DATE_ADD(CURDATE(),INTERVAL 220 DAY),1,1),
(8,1,'Toyota','Sienna','XLE',2022,'H890123','VIN0000000000008','Gris',6,'automatic','hybrid',26000,8,4,4,4500.00,27000.00,99000.00,20000.00,1000.00,'available',2,'Van familiar hibrida, ideal para grupos.',JSON_ARRAY('8 pasajeros','Puertas electricas','Hibrido','A/C tri-zona'),NULL,'toyota-sienna-xle-2022',DATE_ADD(CURDATE(),INTERVAL 70 DAY),DATE_ADD(CURDATE(),INTERVAL 160 DAY),0,1),
(9,1,'Tesla','Model 3','Long Range',2023,'I901234','VIN0000000000009','Blanco',10,'automatic','electric',9000,5,4,2,6000.00,36000.00,132000.00,35000.00,1200.00,'available',1,'100% electrico, autonomia extendida.',JSON_ARRAY('Autopilot','Pantalla 15"','Carga rapida','Glass roof'),NULL,'tesla-model-3-long-range-2023',DATE_ADD(CURDATE(),INTERVAL 150 DAY),DATE_ADD(CURDATE(),INTERVAL 240 DAY),1,1),
(10,1,'Chevrolet','Camaro','SS',2022,'J012345','VIN0000000000010','Amarillo',9,'automatic','gasoline',18000,4,2,1,8000.00,48000.00,176000.00,45000.00,1800.00,'available',1,'Deportivo V8 para una experiencia unica.',JSON_ARRAY('V8','Modo sport','Escape deportivo','Cuero'),NULL,'chevrolet-camaro-ss-2022',DATE_ADD(CURDATE(),INTERVAL 100 DAY),DATE_ADD(CURDATE(),INTERVAL 200 DAY),1,1);

-- ---------------------------------------------------------------------
-- CUSTOMERS (5)
-- ---------------------------------------------------------------------
DELETE FROM `customers` WHERE `tenant_id` = 1;
INSERT INTO `customers`
 (`id`,`tenant_id`,`first_name`,`last_name`,`document_type`,`document_number`,`nationality`,`phone`,`whatsapp`,`email`,`address`,`license_number`,`license_expiration`,`risk_level`,`status`)
VALUES
(1,1,'Pedro','Martinez','cedula','001-1234567-8','Dominicana','+1 809 111 2222','18091112222','pedro@example.com','Calle 1, SD','LIC-001',DATE_ADD(CURDATE(),INTERVAL 400 DAY),'low','active'),
(2,1,'Maria','Gomez','cedula','002-7654321-0','Dominicana','+1 809 333 4444','18093334444','maria@example.com','Calle 2, SD','LIC-002',DATE_ADD(CURDATE(),INTERVAL 200 DAY),'low','active'),
(3,1,'John','Smith','passport','US987654','Estadounidense','+1 305 555 7777','13055557777','john@example.com','Miami, FL','USDL-7788',DATE_ADD(CURDATE(),INTERVAL 600 DAY),'medium','active'),
(4,1,'Laura','Perez','cedula','003-1112223-4','Dominicana','+1 809 555 8888','18095558888','laura@example.com','Santiago','LIC-004',DATE_SUB(CURDATE(),INTERVAL 10 DAY),'high','active'),
(5,1,'Roberto','Diaz','cedula','004-9998887-6','Dominicana','+1 809 777 6666','18097776666','roberto@example.com','La Romana','LIC-005',DATE_ADD(CURDATE(),INTERVAL 90 DAY),'low','blacklist');

-- ---------------------------------------------------------------------
-- RESERVATIONS (8)
-- ---------------------------------------------------------------------
DELETE FROM `reservations` WHERE `tenant_id` = 1;
INSERT INTO `reservations`
 (`id`,`tenant_id`,`reservation_code`,`customer_id`,`vehicle_id`,`start_datetime`,`end_datetime`,`pickup_location`,`return_location`,`daily_rate`,`days_count`,`subtotal`,`discount_amount`,`tax_amount`,`deposit_amount`,`extras_total`,`total_amount`,`status`,`source`,`lead_name`,`lead_phone`,`created_by`)
VALUES
(1,1,'RSV-2026-0001',1,3,DATE_SUB(NOW(),INTERVAL 2 DAY),DATE_ADD(NOW(),INTERVAL 3 DAY),'Sucursal Principal','Sucursal Principal',3200.00,5,16000.00,0.00,2880.00,15000.00,0.00,18880.00,'converted','internal','Pedro Martinez','+1 809 111 2222',1),
(2,1,'RSV-2026-0002',2,1,DATE_ADD(NOW(),INTERVAL 1 DAY),DATE_ADD(NOW(),INTERVAL 4 DAY),'Sucursal Principal','Aeropuerto Las Americas',2200.00,3,6600.00,0.00,1188.00,10000.00,200.00,7988.00,'confirmed','public','Maria Gomez','+1 809 333 4444',NULL),
(3,1,'RSV-2026-0003',3,7,DATE_ADD(NOW(),INTERVAL 2 DAY),DATE_ADD(NOW(),INTERVAL 6 DAY),'Aeropuerto Las Americas','Aeropuerto Las Americas',7500.00,4,30000.00,0.00,5400.00,40000.00,1000.00,36400.00,'pending','public','John Smith','+1 305 555 7777',NULL),
(4,1,'RSV-2026-0004',NULL,9,DATE_ADD(NOW(),INTERVAL 5 DAY),DATE_ADD(NOW(),INTERVAL 8 DAY),'Sucursal Principal','Sucursal Principal',6000.00,3,18000.00,0.00,3240.00,35000.00,0.00,21240.00,'pending','public','Carlos Visitante','+1 809 222 3333',NULL),
(5,1,'RSV-2026-0005',4,6,DATE_SUB(NOW(),INTERVAL 10 DAY),DATE_SUB(NOW(),INTERVAL 7 DAY),'Sucursal Principal','Sucursal Principal',1800.00,3,5400.00,0.00,972.00,8000.00,0.00,6372.00,'finished','internal','Laura Perez','+1 809 555 8888',2),
(6,1,'RSV-2026-0006',2,10,DATE_ADD(NOW(),INTERVAL 10 DAY),DATE_ADD(NOW(),INTERVAL 12 DAY),'Sucursal Principal','Sucursal Principal',8000.00,2,16000.00,1000.00,2700.00,45000.00,0.00,17700.00,'confirmed','internal','Maria Gomez','+1 809 333 4444',1),
(7,1,'RSV-2026-0007',1,2,DATE_SUB(NOW(),INTERVAL 20 DAY),DATE_SUB(NOW(),INTERVAL 18 DAY),'Sucursal Principal','Sucursal Principal',2400.00,2,4800.00,0.00,864.00,10000.00,0.00,5664.00,'cancelled','public','Pedro Martinez','+1 809 111 2222',NULL),
(8,1,'RSV-2026-0008',3,8,DATE_ADD(NOW(),INTERVAL 3 DAY),DATE_ADD(NOW(),INTERVAL 7 DAY),'Aeropuerto Las Americas','Aeropuerto Las Americas',4500.00,4,18000.00,0.00,3240.00,20000.00,1000.00,22240.00,'pending','public','John Smith','+1 305 555 7777',NULL);

-- ---------------------------------------------------------------------
-- CONTRACTS (4)
-- ---------------------------------------------------------------------
DELETE FROM `contracts` WHERE `tenant_id` = 1;
INSERT INTO `contracts`
 (`id`,`tenant_id`,`contract_number`,`reservation_id`,`customer_id`,`vehicle_id`,`start_datetime`,`end_datetime`,`start_mileage`,`start_fuel_level`,`daily_rate`,`subtotal`,`deposit_amount`,`insurance_amount`,`tax_amount`,`total_amount`,`paid_amount`,`balance_due`,`status`,`created_by`)
VALUES
(1,1,'CTR-2026-0001',1,1,3,DATE_SUB(NOW(),INTERVAL 2 DAY),DATE_ADD(NOW(),INTERVAL 3 DAY),15000,100,3200.00,16000.00,15000.00,3500.00,2880.00,18880.00,10000.00,8880.00,'active',1),
(2,1,'CTR-2026-0002',5,4,6,DATE_SUB(NOW(),INTERVAL 10 DAY),DATE_SUB(NOW(),INTERVAL 7 DAY),44000,80,1800.00,5400.00,8000.00,1200.00,972.00,6372.00,6372.00,0.00,'finished',2),
(3,1,'CTR-2026-0003',NULL,2,4,DATE_SUB(NOW(),INTERVAL 1 DAY),DATE_ADD(NOW(),INTERVAL 2 DAY),28000,90,3000.00,9000.00,15000.00,2100.00,1620.00,10620.00,5000.00,5620.00,'active',1),
(4,1,'CTR-2026-0004',NULL,3,5,DATE_SUB(NOW(),INTERVAL 30 DAY),DATE_SUB(NOW(),INTERVAL 25 DAY),21000,100,4000.00,20000.00,20000.00,4500.00,3600.00,23600.00,15000.00,8600.00,'overdue',1);

-- ---------------------------------------------------------------------
-- PAYMENTS (5)
-- ---------------------------------------------------------------------
DELETE FROM `payments` WHERE `tenant_id` = 1;
INSERT INTO `payments`
 (`tenant_id`,`payment_code`,`customer_id`,`reservation_id`,`contract_id`,`amount`,`method`,`reference`,`payment_date`,`status`,`received_by`)
VALUES
(1,'PAY-2026-0001',1,1,1,10000.00,'card','AUTH-9981',CURDATE(),'paid',1),
(1,'PAY-2026-0002',4,5,2,6372.00,'cash',NULL,DATE_SUB(CURDATE(),INTERVAL 7 DAY),'paid',2),
(1,'PAY-2026-0003',2,NULL,3,5000.00,'transfer','TRX-22910',DATE_SUB(CURDATE(),INTERVAL 1 DAY),'paid',1),
(1,'PAY-2026-0004',3,NULL,4,15000.00,'card','AUTH-5520',DATE_SUB(CURDATE(),INTERVAL 28 DAY),'paid',1),
(1,'PAY-2026-0005',2,2,NULL,2000.00,'transfer','TRX-30011',CURDATE(),'pending',NULL);

-- ---------------------------------------------------------------------
-- MAINTENANCE (3)
-- ---------------------------------------------------------------------
DELETE FROM `maintenance_records` WHERE `tenant_id` = 1;
INSERT INTO `maintenance_records`
 (`tenant_id`,`vehicle_id`,`maintenance_type`,`description`,`provider`,`cost`,`mileage`,`start_date`,`end_date`,`next_due_date`,`next_due_mileage`,`status`)
VALUES
(1,5,'mechanical','Reparacion de transmision y revision general','Taller Central',18500.00,22000,CURDATE(),NULL,NULL,NULL,'in_progress'),
(1,1,'oil','Cambio de aceite y filtros','Lubricantes RD',2500.00,18000,DATE_SUB(CURDATE(),INTERVAL 15 DAY),DATE_SUB(CURDATE(),INTERVAL 15 DAY),DATE_ADD(CURDATE(),INTERVAL 75 DAY),23000,'completed'),
(1,3,'tires','Rotacion y balanceo de gomas','Goodyear Service',4200.00,14000,DATE_SUB(CURDATE(),INTERVAL 30 DAY),DATE_SUB(CURDATE(),INTERVAL 30 DAY),DATE_ADD(CURDATE(),INTERVAL 150 DAY),30000,'completed');

-- ---------------------------------------------------------------------
-- VEHICLE IMAGES (placeholders - main_image points to public asset)
-- ---------------------------------------------------------------------
DELETE FROM `vehicle_images` WHERE `tenant_id` = 1;

-- ---------------------------------------------------------------------
-- NOTIFICATIONS (sample for owner)
-- ---------------------------------------------------------------------
DELETE FROM `notifications` WHERE `tenant_id` = 1;
INSERT INTO `notifications` (`tenant_id`,`user_id`,`title`,`message`,`type`,`is_read`,`action_url`) VALUES
(1,1,'Nueva reserva publica','John Smith solicito una reserva del Mercedes-Benz C300','reservation',0,'/admin/reservations'),
(1,1,'Documento por vencer','El marbete del Nissan Versa vence en 5 dias','document',0,'/admin/vehicles/edit/6'),
(1,1,'Contrato en mora','El contrato CTR-2026-0004 esta vencido','contract',0,'/admin/contracts');

-- ---------------------------------------------------------------------
-- EXPENSES (operating costs)
-- ---------------------------------------------------------------------
DELETE FROM `expenses` WHERE `tenant_id` = 1;
INSERT INTO `expenses` (`tenant_id`,`location_id`,`vehicle_id`,`category`,`description`,`amount`,`expense_date`,`payment_method`,`vendor`) VALUES
(1,1,1,'fuel','Combustible flotilla semana 1',8500.00,DATE_SUB(CURDATE(),INTERVAL 3 DAY),'cash','Estacion Shell'),
(1,1,3,'repairs','Cambio de frenos Toyota Corolla',6200.00,DATE_SUB(CURDATE(),INTERVAL 6 DAY),'card','Taller Mecanico RD'),
(1,2,NULL,'rent','Alquiler local aeropuerto',45000.00,DATE_SUB(CURDATE(),INTERVAL 10 DAY),'transfer','Inmobiliaria Caribe'),
(1,1,NULL,'salaries','Nomina quincena agentes',62000.00,DATE_SUB(CURDATE(),INTERVAL 12 DAY),'transfer',NULL),
(1,1,5,'insurance','Poliza seguro Honda CR-V',9800.00,DATE_SUB(CURDATE(),INTERVAL 15 DAY),'transfer','Seguros Universal'),
(1,1,NULL,'marketing','Campana redes sociales',12000.00,DATE_SUB(CURDATE(),INTERVAL 18 DAY),'card','Meta Ads'),
(1,2,7,'fuel','Combustible entregas',4300.00,DATE_SUB(CURDATE(),INTERVAL 20 DAY),'cash',NULL),
(1,1,NULL,'utilities','Electricidad e internet',7600.00,DATE_SUB(CURDATE(),INTERVAL 22 DAY),'transfer','EDESUR'),
(1,1,2,'maintenance','Mantenimiento preventivo 10k',5400.00,DATE_SUB(CURDATE(),INTERVAL 25 DAY),'card','Taller Mecanico RD'),
(1,1,NULL,'taxes','Impuestos municipales',8900.00,DATE_SUB(CURDATE(),INTERVAL 28 DAY),'transfer','Ayuntamiento');

SET FOREIGN_KEY_CHECKS = 1;
