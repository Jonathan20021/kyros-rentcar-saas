<?php
namespace App\Services;

use App\Core\Database;
use App\Core\Logger;
use App\Models\DemoLicense;
use App\Models\Tenant;
use App\Models\User;

/**
 * DemoService — spins up disposable demo tenants and purges them on expiry.
 *
 * On redeem(): creates a tenant (status='trial', is_demo=1, demo_expires_at=NOW+hours),
 * an owner user, and a curated set of starter data so the demo is immediately usable.
 *
 * On sweep(): hard-deletes every demo tenant whose `demo_expires_at` has passed.
 * FK CASCADE handles the bulk of related tables; a few non-cascading ones are
 * cleaned by hand.
 */
class DemoService
{
    /** Spin up a demo tenant. Returns [tenantId, ownerUserId, tenantSlug]. */
    public static function redeem(array $license, string $name, string $email, string $password): array
    {
        if (!DemoLicense::isUsable($license)) {
            throw new \RuntimeException('Esta licencia ya no está disponible.');
        }
        $hours = max(1, (int) $license['hours_valid']);
        $expires = date('Y-m-d H:i:s', time() + $hours * 3600);

        Database::beginTransaction();
        try {
            // Build a unique slug.
            $base = self::slugify($name) ?: 'demo';
            $slug = $base;
            $i = 1;
            while (Database::scalar("SELECT 1 FROM tenants WHERE slug = :s", ['s' => $slug])) {
                $slug = $base . '-' . $i++;
            }

            $tenantId = Tenant::create([
                'name'              => $name,
                'slug'              => $slug,
                'email'             => strtolower($email),
                'phone'             => null,
                'address'           => 'Demo · Santo Domingo',
                'description'       => 'Demo de Kyros Rent Car generado por licencia ' . $license['code'] . '.',
                'primary_color'     => '#F23645',
                'secondary_color'   => '#1C2433',
                'currency'          => 'DOP',
                'tax_rate'          => 18.00,
                'plan_id'           => (int) $license['plan_id'],
                'status'            => 'trial',
                'is_demo'           => 1,
                'demo_expires_at'   => $expires,
                'demo_license_code' => $license['code'],
                'trial_ends_at'     => date('Y-m-d', strtotime($expires)),
            ]);

            $userId = User::create([
                'tenant_id'         => $tenantId,
                'role_id'           => 2,           // owner
                'name'              => $name,
                'email'             => strtolower($email),
                'password'          => password_hash($password, PASSWORD_BCRYPT),
                'status'            => 'active',
                'email_verified_at' => date('Y-m-d H:i:s'),
            ]);

            self::seedStarterData($tenantId);
            DemoLicense::incrementUse((int) $license['id']);

            Database::commit();
            return [$tenantId, $userId, $slug];
        } catch (\Throwable $e) {
            Database::rollBack();
            Logger::error('Demo redeem failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Curated starter data so the demo is immediately useful AND looks alive.
     * Seeds a full operating month: ~15 vehicles with real photos, ~12 customers,
     * drivers, promo codes, reservations in every status, signed contracts,
     * payments, invoices, expenses, maintenance, incidents and notifications.
     */
    protected static function seedStarterData(int $tid): void
    {
        $pdo = Database::connection();
        $lastId = fn() => (int) $pdo->lastInsertId();

        // --- Categories ----------------------------------------------------
        $cats = [
            ['Económico', 'car'],
            ['Compacto',  'car'],
            ['Sedán',     'car'],
            ['SUV',       'truck'],
            ['Pickup',    'truck'],
            ['Van',       'bus'],
            ['Lujo',      'gem'],
            ['Deportivo',  'zap'],
        ];
        $catIds = [];
        foreach ($cats as $row) {
            Database::execute(
                "INSERT INTO vehicle_categories (tenant_id, name, slug, icon, status)
                 VALUES (:t, :n, :s, :i, 'active')",
                ['t' => $tid, 'n' => $row[0], 's' => self::slugify($row[0]), 'i' => $row[1]]
            );
            $catIds[$row[0]] = $lastId();
        }

        // --- Locations -----------------------------------------------------
        $locations = [
            ['Sucursal Principal', 'Av. Winston Churchill 95, Santo Domingo', '+1 809 555 0100', 'Carlos Méndez'],
            ['Aeropuerto SDQ',     'Aeropuerto Las Américas, Mostrador 12',   '+1 809 555 0200', 'Lucía Almonte'],
            ['Sucursal Norte',     'Av. 27 de Febrero, Santiago',             '+1 809 555 0300', 'Pedro Ramírez'],
        ];
        $locIds = [];
        foreach ($locations as $L) {
            Database::execute(
                "INSERT INTO locations (tenant_id, name, address, phone, manager_name, status)
                 VALUES (:t, :n, :a, :p, :m, 'active')",
                ['t'=>$tid,'n'=>$L[0],'a'=>$L[1],'p'=>$L[2],'m'=>$L[3]]
            );
            $locIds[] = $lastId();
        }

        // --- Vehicles (15) ------------------------------------------------
        // Each entry: [brand, model, version, year, plate, vin, color, daily, weekly, monthly, deposit, insurance,
        //              mileage, passengers, doors, cat, transmission, fuel, status, location_idx, is_featured]
        // Photos are bundled JPG assets under /assets/demo/vehicles/*.jpg —
        // always available, no external CDN, consistent look across the demo.
        $catToImage = [
            'Económico' => '/assets/demo/vehicles/toyota-corolla.jpg',
            'Compacto'  => '/assets/demo/vehicles/honda-civic.jpg',
            'Sedán'     => '/assets/demo/vehicles/toyota-corolla.jpg',
            'SUV'       => '/assets/demo/vehicles/hyundai-tucson.jpg',
            'Pickup'    => '/assets/demo/vehicles/toyota-hilux-real.jpg',
            'Van'       => '/assets/demo/vehicles/kia-sportage.jpg',
            'Lujo'      => '/assets/demo/vehicles/mercedes-c-class.jpg',
        ];
        $vehicles = [
            ['Toyota','Corolla','SE',2021,'A100-001','VINTOY00001','Negro', 2200, 13200, 50000, 10000, 350, 18500, 5, 4, 'Sedán',     'automatic','gasoline','available',     0, 0, '/assets/demo/vehicles/toyota-corolla.jpg'],
            ['Honda','Civic','Sport',2022,'H200-002','VINHON00002','Gris', 2400, 14400, 56000, 12000, 380, 22000, 5, 4, 'Sedán',     'automatic','gasoline','available',     0, 0, '/assets/demo/vehicles/honda-civic.jpg'],
            ['Hyundai','Tucson','Limited',2023,'B300-003','VINHYU00003','Negro', 3500, 21000, 80000, 15000, 480, 12000, 5, 5, 'SUV',     'automatic','gasoline','rented',        0, 1, '/assets/demo/vehicles/hyundai-tucson.jpg'],
            ['Kia','Sportage','HEV',2023,'P400-004','VINKIA00004','Negro', 3000, 18000, 66000, 15000, 420, 26000, 5, 5, 'SUV',  'automatic','hybrid','available',     1, 0, '/assets/demo/vehicles/kia-sportage.jpg'],
            ['Mercedes-Benz','C-Class','C300',2022,'L500-005','VINMRC00005','Blanco', 8500, 51000, 195000, 30000, 950, 8000, 5, 4, 'Lujo','automatic','gasoline','available',     0, 1, '/assets/demo/vehicles/mercedes-c-class.jpg'],
            ['Toyota','Hilux','Doble Cabina',2021,'T600-006','VINTOY00006','Negro', 4200, 25200, 98000, 18000, 580, 14500, 5, 4, 'Pickup',          'automatic','diesel',  'available',     1, 0, '/assets/demo/vehicles/toyota-hilux-real.jpg'],
            ['BMW','4 Series','420i M Sport',2022,'N700-007','VINBMW00007','Negro', 8200, 49200, 188000, 32000, 920, 30500, 4, 2, 'Lujo',  'automatic','gasoline','maintenance',   0, 0, '/assets/demo/vehicles/bmw-4-series.jpg'],
            ['Chevrolet','Camaro','LT',2021,'C800-008','VINCHV00008','Azul', 7600, 45600, 172000, 36000, 1050, 35200, 4, 2, 'Deportivo','automatic','gasoline','available',     2, 0, '/assets/demo/vehicles/chevrolet-camaro.jpg'],
            ['Ford','Mustang','EcoBoost',2020,'F900-009','VINFRD00009','Negro', 7200, 43200, 158000, 35000, 980, 18900, 4, 2, 'Deportivo', 'automatic','gasoline','available',     0, 1, '/assets/demo/vehicles/ford-mustang.jpg'],
            ['Tesla','Model 3','Long Range',2023,'V100-010','VINTES00010','Blanco', 6000, 36000, 132000, 30000, 850, 42100, 5, 4, 'Lujo','automatic','electric',  'available',     0, 0, '/assets/demo/vehicles/tesla-model-3.jpg'],
            ['Honda','Civic','Type R',2021,'X200-011','VINHON00011','Gris', 3600, 21600, 84000, 15000, 520, 9500, 5, 4, 'Sedán',          'manual','gasoline','reserved',      0, 1, '/assets/demo/vehicles/honda-civic.jpg'],
            ['Kia','Sportage','EX',2022,'M300-012','VINKIA00012','Negro', 3200, 19200, 74000, 15000, 460, 11200, 5, 5, 'SUV',         'automatic','gasoline','available',     1, 0, '/assets/demo/vehicles/kia-sportage.jpg'],
            ['Toyota','Corolla','LE',2020,'J400-013','VINTOY00013','Negro', 2100, 12600, 48000, 10000, 320, 24700, 5, 4, 'Sedán','automatic','gasoline','available', 2, 0, '/assets/demo/vehicles/toyota-corolla.jpg'],
            ['Toyota','Hilux','SR5',2022,'H500-014','VINTOY00014','Negro', 4800, 28800, 112000, 22000, 660, 16800, 5, 4, 'Pickup', 'automatic','diesel',  'available',     2, 0, '/assets/demo/vehicles/toyota-hilux-real.jpg'],
            ['Mercedes-Benz','C-Class','AMG Line',2021,'Q600-015','VINMRC00015','Blanco', 7800, 46800, 180000, 28000, 880, 10100, 5, 4, 'Lujo',          'automatic','gasoline','available',     0, 1, '/assets/demo/vehicles/mercedes-c-class.jpg'],
        ];
        $vehicleIds = [];
        foreach ($vehicles as $idx => $v) {
            [$brand,$model,$ver,$year,$plate,$vin,$color,$daily,$weekly,$monthly,$dep,$ins,$mi,$pax,$doors,$catName,$trans,$fuel,$status,$locIdx,$feat,$image] = $v;
            $catId = $catIds[$catName] ?? null;
            $mainImg = $image ?: ($catToImage[$catName] ?? '/assets/demo/vehicles/toyota-corolla.jpg');
            $gallery = []; // one image per category is enough — keeps demo tight & on-brand
            $loc   = $locIds[$locIdx] ?? $locIds[0];
            $desc  = "Vehículo $brand $model $year en excelentes condiciones. " . ucfirst($trans) . ", " . $pax . " pasajeros, " . $doors . " puertas. Mantenimiento al día.";
            $features = json_encode(['A/C','Bluetooth','Cámara reversa','USB','Cierre central','Bolsas de aire'], JSON_UNESCAPED_UNICODE);
            Database::execute(
                "INSERT INTO vehicles (tenant_id, brand, model, version, year, plate_number, vin, color, category_id,
                                       transmission, fuel_type, mileage, passengers, doors, daily_price, weekly_price, monthly_price,
                                       deposit_amount, insurance_price, status, location_id, slug, is_public, is_featured,
                                       main_image, description, features,
                                       insurance_expires, marbete_expires, plate_expires, inspection_expires)
                 VALUES (:t,:b,:m,:v,:y,:pl,:vin,:c,:cat,:tr,:fu,:mi,:p,:dr,:d,:wp,:mp,:dep,:ins,:st,:loc,:s,1,:feat,
                         :img,:desc,:feats,
                         :insExp,:marExp,:platExp,:inspExp)",
                ['t'=>$tid,'b'=>$brand,'m'=>$model,'v'=>$ver,'y'=>$year,'pl'=>$plate,'vin'=>$vin,'c'=>$color,'cat'=>$catId,
                 'tr'=>$trans,'fu'=>$fuel,'mi'=>$mi,'p'=>$pax,'dr'=>$doors,'d'=>$daily,'wp'=>$weekly,'mp'=>$monthly,'dep'=>$dep,'ins'=>$ins,
                 'st'=>$status,'loc'=>$loc,'s'=>self::slugify($brand.'-'.$model.'-'.$ver.'-'.$year.'-'.$idx),
                 'feat'=>$feat,'img'=>$mainImg,'desc'=>$desc,'feats'=>$features,
                 'insExp'=>date('Y-m-d', strtotime('+' . (30 + $idx * 12) . ' days')),
                 'marExp'=>date('Y-m-d', strtotime('+' . (60 + $idx * 10) . ' days')),
                 'platExp'=>date('Y-m-d', strtotime('+' . (120 + $idx * 8) . ' days')),
                 'inspExp'=>date('Y-m-d', strtotime('+' . (45 + $idx * 9) . ' days'))]
            );
            $vid = $lastId();
            $vehicleIds[] = $vid;

            // Gallery rows
            $order = 0;
            Database::execute(
                "INSERT INTO vehicle_images (tenant_id, vehicle_id, path, is_main, sort_order)
                 VALUES (:t,:v,:p,1,:o)",
                ['t'=>$tid,'v'=>$vid,'p'=>$mainImg,'o'=>$order++]
            );
            foreach ($gallery as $g) {
                Database::execute(
                    "INSERT INTO vehicle_images (tenant_id, vehicle_id, path, is_main, sort_order)
                     VALUES (:t,:v,:p,0,:o)",
                    ['t'=>$tid,'v'=>$vid,'p'=>$g,'o'=>$order++]
                );
            }
        }

        // --- Extras --------------------------------------------------------
        $extras = [
            ['GPS premium','Navegador GPS portátil con mapas RD actualizados', 250,'per_day'],
            ['Silla de bebé','Silla de seguridad infantil homologada',          300,'per_day'],
            ['Silla booster','Silla elevadora para niños 4-12 años',            250,'per_day'],
            ['Conductor adicional','Segundo conductor autorizado en el contrato',400,'per_reservation'],
            ['Entrega a domicilio','Te llevamos el vehículo donde lo necesites',800,'one_time'],
            ['Entrega en aeropuerto','Recogida directa en SDQ o STI',           1200,'one_time'],
            ['WiFi 4G ilimitado','Hotspot WiFi con datos ilimitados',           350,'per_day'],
            ['Seguro premium','Cobertura ampliada con cero deducible',          600,'per_day'],
        ];
        foreach ($extras as $ex) {
            Database::execute(
                "INSERT INTO extras (tenant_id, name, description, price, charge_type, status)
                 VALUES (:t, :n, :d, :p, :ct, 'active')",
                ['t'=>$tid,'n'=>$ex[0],'d'=>$ex[1],'p'=>$ex[2],'ct'=>$ex[3]]
            );
        }

        // --- Customers (12) -----------------------------------------------
        $customers = [
            ['María','Pérez Méndez',    'cedula','001-1111111-1','+1 809 555 1001','maria.perez@demo.com',  'LIC-DR-001','Calle El Conde 45, Zona Colonial, SD'],
            ['Juan','Rodríguez Castillo','cedula','002-2222222-2','+1 809 555 1002','juan.rodriguez@demo.com','LIC-DR-002','Av. Bolívar 102, Gascue, SD'],
            ['Laura','Santos Núñez',    'passport','PA00012345',  '+1 809 555 1003','laura.santos@demo.com', 'LIC-DR-003','Av. Anacaona 88, Bella Vista, SD'],
            ['Carlos','Jiménez Mota',   'cedula','003-3333333-3','+1 809 555 1004','carlos.j@demo.com',     'LIC-DR-004','Plaza Naco, Torre 3, SD'],
            ['Ana','Martínez López',    'cedula','004-4444444-4','+1 809 555 1005','ana.martinez@demo.com', 'LIC-DR-005','Av. Independencia 250, SD'],
            ['Roberto','Vargas Cruz',   'cedula','005-5555555-5','+1 809 555 1006','rvargas@demo.com',      'LIC-DR-006','Calle Juan Pablo Duarte 12, Santiago'],
            ['Patricia','García Reyes', 'passport','PA00067890', '+1 809 555 1007','patty.g@demo.com',      'LIC-DR-007','Av. JFK 67, Los Cacicazgos, SD'],
            ['Miguel','Hernández Polanco','cedula','006-6666666-6','+1 829 555 1008','miguel.h@demo.com',   'LIC-DR-008','Av. Las Américas km 8, SD Este'],
            ['Sofía','Reyes Bautista',  'cedula','007-7777777-7','+1 809 555 1009','sofia.r@demo.com',      'LIC-DR-009','Av. Sarasota 35, Bella Vista, SD'],
            ['Diego','Fernández Tavárez','cedula','008-8888888-8','+1 829 555 1010','diego.f@demo.com',     'LIC-DR-010','Av. Lope de Vega 13, Naco, SD'],
            ['Valeria','Castro Beltré', 'passport','PA00099999', '+1 849 555 1011','vale.c@demo.com',       'LIC-DR-011','Calle El Sol 200, Santiago'],
            ['Andrés','Núñez Beriguete','cedula','009-9999999-9','+1 809 555 1012','andres.n@demo.com',     'LIC-DR-012','Av. Estrella Sadhalá 90, Santiago'],
        ];
        $custIds = [];
        foreach ($customers as $c) {
            Database::execute(
                "INSERT INTO customers (tenant_id, first_name, last_name, document_type, document_number,
                                        phone, email, license_number, address, status)
                 VALUES (:t,:f,:l,:dt,:dn,:p,:e,:lic,:a,'active')",
                ['t'=>$tid,'f'=>$c[0],'l'=>$c[1],'dt'=>$c[2],'dn'=>$c[3],'p'=>$c[4],'e'=>$c[5],'lic'=>$c[6],'a'=>$c[7]]
            );
            $custIds[] = $lastId();
        }

        // --- Drivers -------------------------------------------------------
        $drivers = [
            ['Pedro',  'Almonte',   '010-0000001-0','LIC-CHF-001','+1 809 555 2001','pedro.almonte@demo.com', 1500, 200, 4.9],
            ['José',   'Polanco',   '010-0000002-0','LIC-CHF-002','+1 809 555 2002','jose.polanco@demo.com',  1500, 200, 4.7],
            ['Rafael', 'Brito',     '010-0000003-0','LIC-CHF-003','+1 809 555 2003','rafael.brito@demo.com',  1800, 250, 5.0],
            ['Luis',   'Disla',     '010-0000004-0','LIC-CHF-004','+1 809 555 2004','luis.disla@demo.com',    1500, 200, 4.6],
        ];
        $driverIds = [];
        foreach ($drivers as $d) {
            Database::execute(
                "INSERT INTO drivers (tenant_id, first_name, last_name, document_number, license_number,
                                      phone, email, daily_rate, hourly_rate, rating, status)
                 VALUES (:t,:f,:l,:dn,:lic,:p,:e,:dr,:hr,:rt,'active')",
                ['t'=>$tid,'f'=>$d[0],'l'=>$d[1],'dn'=>$d[2],'lic'=>$d[3],'p'=>$d[4],'e'=>$d[5],
                 'dr'=>$d[6],'hr'=>$d[7],'rt'=>$d[8]]
            );
            $driverIds[] = $lastId();
        }

        // --- Promo codes ---------------------------------------------------
        $promos = [
            ['BIENVENIDO10','10% de descuento para nuevos clientes',  'percent', 10, 0,    null, '+90 days'],
            ['VERANO2026',  'Verano 2026 — 15% en SUV y Lujo',         'percent', 15, 8000, 100,  '+60 days'],
            ['FINDE500',    'RD\$ 500 off en alquileres de fin de semana','fixed', 500, 3000, 200,  '+30 days'],
        ];
        foreach ($promos as $p) {
            Database::execute(
                "INSERT INTO promo_codes (tenant_id, code, description, discount_type, discount_value,
                                          min_amount, max_uses, used_count, valid_from, valid_to, is_public)
                 VALUES (:t,:c,:d,:dt,:dv,:min,:mx,0,CURDATE(),:to,1)",
                ['t'=>$tid,'c'=>$p[0],'d'=>$p[1],'dt'=>$p[2],'dv'=>$p[3],'min'=>$p[4],'mx'=>$p[5],
                 'to'=>date('Y-m-d', strtotime($p[6]))]
            );
        }

        // --- Reservations across statuses ---------------------------------
        // Helper to insert and return id
        $insertReservation = function (array $r) use ($tid, $lastId) {
            Database::execute(
                "INSERT INTO reservations (tenant_id, reservation_code, customer_id, vehicle_id,
                                           start_datetime, end_datetime, pickup_location, return_location,
                                           daily_rate, days_count, subtotal, tax_amount, deposit_amount,
                                           total_amount, status, source, lead_name, lead_phone, lead_email, notes)
                 VALUES (:t,:code,:c,:v,:s,:e,:pu,:rl,:dr,:dc,:sub,:tax,:dep,:tot,:st,:src,:ln,:lp,:le,:nt)",
                ['t'=>$tid,'code'=>$r['code'],'c'=>$r['c'],'v'=>$r['v'],'s'=>$r['s'],'e'=>$r['e'],
                 'pu'=>$r['pu'] ?? 'Sucursal Principal','rl'=>$r['rl'] ?? 'Sucursal Principal',
                 'dr'=>$r['dr'],'dc'=>$r['dc'],'sub'=>$r['sub'],'tax'=>$r['tax'],'dep'=>$r['dep'] ?? 0,
                 'tot'=>$r['tot'],'st'=>$r['st'],'src'=>$r['src'] ?? 'internal',
                 'ln'=>$r['ln'] ?? null,'lp'=>$r['lp'] ?? null,'le'=>$r['le'] ?? null,'nt'=>$r['nt'] ?? null]
            );
            return $lastId();
        };

        $reservations = [];
        // 1: pending public lead — future
        $reservations[] = $insertReservation([
            'code'=>'RSV-DEMO-0001','c'=>$custIds[0],'v'=>$vehicleIds[1],
            's'=>date('Y-m-d', strtotime('+5 days')) . ' 10:00:00',
            'e'=>date('Y-m-d', strtotime('+8 days')) . ' 10:00:00',
            'dr'=>2400,'dc'=>3,'sub'=>7200,'tax'=>1296,'dep'=>12000,'tot'=>8496,
            'st'=>'pending','src'=>'public',
            'ln'=>'María Pérez','lp'=>'+1 809 555 1001','le'=>'maria.perez@demo.com',
            'nt'=>'Cliente solicita entrega en aeropuerto.',
        ]);
        // 2: confirmed — near future
        $reservations[] = $insertReservation([
            'code'=>'RSV-DEMO-0002','c'=>$custIds[1],'v'=>$vehicleIds[3],
            's'=>date('Y-m-d', strtotime('+2 days')) . ' 09:00:00',
            'e'=>date('Y-m-d', strtotime('+5 days')) . ' 09:00:00',
            'dr'=>1800,'dc'=>3,'sub'=>5400,'tax'=>972,'dep'=>8000,'tot'=>6372,
            'st'=>'confirmed','src'=>'internal',
        ]);
        // 3: in_progress — currently rented (vehicle 3 - Tucson)
        $reservations[] = $insertReservation([
            'code'=>'RSV-DEMO-0003','c'=>$custIds[2],'v'=>$vehicleIds[2],
            's'=>date('Y-m-d', strtotime('-2 days')) . ' 14:00:00',
            'e'=>date('Y-m-d', strtotime('+4 days')) . ' 14:00:00',
            'dr'=>3500,'dc'=>6,'sub'=>21000,'tax'=>3780,'dep'=>15000,'tot'=>24780,
            'st'=>'in_progress','src'=>'internal',
        ]);
        // 4: finished — last week
        $reservations[] = $insertReservation([
            'code'=>'RSV-DEMO-0004','c'=>$custIds[3],'v'=>$vehicleIds[0],
            's'=>date('Y-m-d', strtotime('-15 days')) . ' 10:00:00',
            'e'=>date('Y-m-d', strtotime('-10 days')) . ' 10:00:00',
            'dr'=>2200,'dc'=>5,'sub'=>11000,'tax'=>1980,'dep'=>10000,'tot'=>12980,
            'st'=>'finished','src'=>'internal',
        ]);
        // 5: finished — long
        $reservations[] = $insertReservation([
            'code'=>'RSV-DEMO-0005','c'=>$custIds[4],'v'=>$vehicleIds[4],
            's'=>date('Y-m-d', strtotime('-25 days')) . ' 12:00:00',
            'e'=>date('Y-m-d', strtotime('-20 days')) . ' 12:00:00',
            'dr'=>8500,'dc'=>5,'sub'=>42500,'tax'=>7650,'dep'=>30000,'tot'=>50150,
            'st'=>'finished','src'=>'internal',
        ]);
        // 6: confirmed — BMW reserved
        $reservations[] = $insertReservation([
            'code'=>'RSV-DEMO-0006','c'=>$custIds[6],'v'=>$vehicleIds[10],
            's'=>date('Y-m-d', strtotime('+12 days')) . ' 10:00:00',
            'e'=>date('Y-m-d', strtotime('+19 days')) . ' 10:00:00',
            'dr'=>9200,'dc'=>7,'sub'=>64400,'tax'=>11592,'dep'=>35000,'tot'=>75992,
            'st'=>'confirmed','src'=>'public',
            'ln'=>'Patricia García','lp'=>'+1 809 555 1007','le'=>'patty.g@demo.com',
        ]);
        // 7: pending — public lead
        $reservations[] = $insertReservation([
            'code'=>'RSV-DEMO-0007','c'=>null,'v'=>$vehicleIds[8],
            's'=>date('Y-m-d', strtotime('+7 days')) . ' 08:00:00',
            'e'=>date('Y-m-d', strtotime('+9 days')) . ' 08:00:00',
            'dr'=>4500,'dc'=>2,'sub'=>9000,'tax'=>1620,'dep'=>20000,'tot'=>10620,
            'st'=>'pending','src'=>'public',
            'ln'=>'Empresa Constructora del Caribe','lp'=>'+1 809 555 9000','le'=>'compras@constructoracaribe.do',
            'nt'=>'Necesitan factura con NCF.',
        ]);
        // 8: cancelled
        $reservations[] = $insertReservation([
            'code'=>'RSV-DEMO-0008','c'=>$custIds[7],'v'=>$vehicleIds[5],
            's'=>date('Y-m-d', strtotime('+4 days')) . ' 09:00:00',
            'e'=>date('Y-m-d', strtotime('+7 days')) . ' 09:00:00',
            'dr'=>3800,'dc'=>3,'sub'=>11400,'tax'=>2052,'dep'=>16000,'tot'=>13452,
            'st'=>'cancelled','src'=>'internal','nt'=>'Cliente canceló por cambio de planes.',
        ]);

        // --- Contracts ----------------------------------------------------
        // Two finished/active contracts based on reservations 3, 4, 5
        $contracts = [];
        $now = date('Y-m-d H:i:s');

        // Contract 1 — finished (reservation 4)
        $start = date('Y-m-d', strtotime('-15 days')) . ' 10:00:00';
        $end   = date('Y-m-d', strtotime('-10 days')) . ' 10:00:00';
        Database::execute(
            "INSERT INTO contracts (tenant_id, contract_number, reservation_id, customer_id, vehicle_id,
                                    start_datetime, end_datetime, actual_return_datetime,
                                    start_mileage, end_mileage, start_fuel_level, end_fuel_level,
                                    daily_rate, subtotal, deposit_amount, tax_amount, total_amount, paid_amount, balance_due,
                                    status, signed_at, signed_ip)
             VALUES (:t,'CTR-DEMO-0001',:r,:c,:v,:s,:e,:ar,18500,18950,100,90,
                     2200,11000,10000,1980,12980,12980,0,
                     'finished',:sg,'127.0.0.1')",
            ['t'=>$tid,'r'=>$reservations[3],'c'=>$custIds[3],'v'=>$vehicleIds[0],
             's'=>$start,'e'=>$end,'ar'=>$end,'sg'=>date('Y-m-d H:i:s', strtotime('-15 days'))]
        );
        $contracts[] = $lastId();

        // Contract 2 — active in_progress (reservation 3)
        $start = date('Y-m-d', strtotime('-2 days')) . ' 14:00:00';
        $end   = date('Y-m-d', strtotime('+4 days')) . ' 14:00:00';
        Database::execute(
            "INSERT INTO contracts (tenant_id, contract_number, reservation_id, customer_id, vehicle_id,
                                    start_datetime, end_datetime, start_mileage, start_fuel_level,
                                    daily_rate, subtotal, deposit_amount, tax_amount, total_amount, paid_amount, balance_due,
                                    status, signed_at, signed_ip)
             VALUES (:t,'CTR-DEMO-0002',:r,:c,:v,:s,:e,12000,100,
                     3500,21000,15000,3780,24780,12390,12390,
                     'active',:sg,'127.0.0.1')",
            ['t'=>$tid,'r'=>$reservations[2],'c'=>$custIds[2],'v'=>$vehicleIds[2],
             's'=>$start,'e'=>$end,'sg'=>date('Y-m-d H:i:s', strtotime('-2 days'))]
        );
        $contracts[] = $lastId();

        // Contract 3 — finished Mercedes (reservation 5)
        $start = date('Y-m-d', strtotime('-25 days')) . ' 12:00:00';
        $end   = date('Y-m-d', strtotime('-20 days')) . ' 12:00:00';
        Database::execute(
            "INSERT INTO contracts (tenant_id, contract_number, reservation_id, customer_id, vehicle_id,
                                    start_datetime, end_datetime, actual_return_datetime,
                                    start_mileage, end_mileage, start_fuel_level, end_fuel_level,
                                    daily_rate, subtotal, deposit_amount, tax_amount, total_amount, paid_amount, balance_due,
                                    status, signed_at, signed_ip)
             VALUES (:t,'CTR-DEMO-0003',:r,:c,:v,:s,:e,:ar,8000,8580,100,75,
                     8500,42500,30000,7650,50150,50150,0,
                     'finished',:sg,'127.0.0.1')",
            ['t'=>$tid,'r'=>$reservations[4],'c'=>$custIds[4],'v'=>$vehicleIds[4],
             's'=>$start,'e'=>$end,'ar'=>$end,'sg'=>date('Y-m-d H:i:s', strtotime('-25 days'))]
        );
        $contracts[] = $lastId();

        // --- Payments ----------------------------------------------------
        $payments = [
            // For contract 1 (finished, fully paid)
            ['CTR-DEMO-0001-PMT-1', $custIds[3], null, $contracts[0], 6490, 'card',     'TXN-AZUL-001', '-15 days', 'paid'],
            ['CTR-DEMO-0001-PMT-2', $custIds[3], null, $contracts[0], 6490, 'cash',     null,           '-10 days', 'paid'],
            // For contract 2 (active, 50% paid)
            ['CTR-DEMO-0002-PMT-1', $custIds[2], null, $contracts[1], 12390,'transfer', 'BHD-987654',   '-2 days',  'paid'],
            // For contract 3 (finished, fully paid)
            ['CTR-DEMO-0003-PMT-1', $custIds[4], null, $contracts[2], 25075,'card',     'TXN-CARDNET-001','-25 days','paid'],
            ['CTR-DEMO-0003-PMT-2', $custIds[4], null, $contracts[2], 25075,'transfer', 'POPULAR-12345','-20 days', 'paid'],
            // Standalone reservation deposit
            ['RSV-DEMO-0002-DEP',   $custIds[1], $reservations[1], null,  2000, 'cash',   null,           '-1 days',  'paid'],
        ];
        foreach ($payments as $p) {
            Database::execute(
                "INSERT INTO payments (tenant_id, payment_code, customer_id, reservation_id, contract_id,
                                       amount, method, reference, payment_date, status)
                 VALUES (:t,:cd,:c,:r,:ct,:a,:m,:ref,:dt,:st)",
                ['t'=>$tid,'cd'=>$p[0],'c'=>$p[1],'r'=>$p[2],'ct'=>$p[3],
                 'a'=>$p[4],'m'=>$p[5],'ref'=>$p[6],
                 'dt'=>date('Y-m-d', strtotime($p[7])),'st'=>$p[8]]
            );
        }

        // --- Invoices -----------------------------------------------------
        $invoices = [
            ['INV-DEMO-0001', $custIds[3], $contracts[0], 11000, 1980, 0,    12980, 'paid',   '-15 days', '-10 days'],
            ['INV-DEMO-0002', $custIds[2], $contracts[1], 21000, 3780, 0,    24780, 'issued', '-2 days',  '+4 days'],
            ['INV-DEMO-0003', $custIds[4], $contracts[2], 42500, 7650, 0,    50150, 'paid',   '-25 days', '-20 days'],
            ['INV-DEMO-0004', $custIds[6], null,           64400, 11592, 0,   75992, 'issued', 'now',      '+10 days'],
        ];
        foreach ($invoices as $inv) {
            Database::execute(
                "INSERT INTO invoices (tenant_id, invoice_number, customer_id, contract_id, subtotal, tax_amount,
                                       discount_amount, total, status, issue_date, due_date)
                 VALUES (:t,:n,:c,:ct,:sub,:tax,:dis,:tot,:st,:iss,:due)",
                ['t'=>$tid,'n'=>$inv[0],'c'=>$inv[1],'ct'=>$inv[2],'sub'=>$inv[3],'tax'=>$inv[4],
                 'dis'=>$inv[5],'tot'=>$inv[6],'st'=>$inv[7],
                 'iss'=>date('Y-m-d', strtotime($inv[8])),'due'=>date('Y-m-d', strtotime($inv[9]))]
            );
        }

        // --- Expenses (last 30 days, varied) ------------------------------
        $expenses = [
            ['fuel',      'Combustible flotilla — semana', 8500, 'transfer', 'Shell Caribe',         '-28 days', $locIds[0], null],
            ['fuel',      'Combustible aeropuerto SDQ',    4200, 'card',     'Texaco',               '-25 days', $locIds[1], null],
            ['salaries',  'Nómina quincena empleados',     65000,'transfer', 'BHD León — Payroll',   '-23 days', null,        null],
            ['maintenance','Cambio de aceite Corolla',     2800, 'cash',     'Taller AutoExpress',   '-22 days', null,        $vehicleIds[0]],
            ['repairs',   'Reparación parachoques Civic',  6500, 'card',     'AutoBody RD',          '-20 days', null,        $vehicleIds[1]],
            ['insurance', 'Seguro mensual flotilla',       28000,'transfer', 'Seguros Banreservas',  '-18 days', null,        null],
            ['utilities', 'Electricidad oficina',          4500, 'transfer', 'Edenorte',             '-15 days', $locIds[0], null],
            ['rent',      'Alquiler local Santo Domingo',  35000,'transfer', 'Inmobiliaria Caribe',  '-14 days', $locIds[0], null],
            ['marketing', 'Publicidad Facebook + Google',  12000,'card',     'Meta + Google',        '-12 days', null,        null],
            ['supplies',  'Productos de limpieza',         3200, 'cash',     'PriceSmart',           '-10 days', null,        null],
            ['fuel',      'Combustible Tucson',            1800, 'cash',     'Esso',                 '-8 days',  null,        $vehicleIds[2]],
            ['maintenance','Inspección técnica Hilux',     1500, 'cash',     'Inspectoría Oficial',  '-5 days',  null,        $vehicleIds[13]],
        ];
        foreach ($expenses as $ex) {
            Database::execute(
                "INSERT INTO expenses (tenant_id, location_id, vehicle_id, category, description, amount,
                                       expense_date, payment_method, vendor)
                 VALUES (:t,:loc,:veh,:cat,:d,:a,:dt,:m,:v)",
                ['t'=>$tid,'loc'=>$ex[6],'veh'=>$ex[7],'cat'=>$ex[0],'d'=>$ex[1],'a'=>$ex[2],
                 'dt'=>date('Y-m-d', strtotime($ex[5])),'m'=>$ex[3],'v'=>$ex[4]]
            );
        }

        // --- Maintenance --------------------------------------------------
        $maintenance = [
            [$vehicleIds[0], 'oil',         'Cambio de aceite + filtro 10K',   'Taller AutoExpress', 2800, 18500, '-22 days', '-22 days', '+38 days', 28500, 'completed'],
            [$vehicleIds[6], 'mechanical',  'Revisión sistema A/C',             'TecniCar RD',        4500, 30500, '-3 days',  null,        '+27 days', 35000, 'in_progress'],
            [$vehicleIds[1], 'tires',       'Rotación + alineación',            'Cassá Llantas',      3800, 22000, '-15 days', '-15 days', '+45 days', 27000, 'completed'],
            [$vehicleIds[4], 'deep_clean',  'Detallado interior + cera',        'Premium Detail',     5500, 8000,  '-12 days', '-12 days', '+78 days', 13000, 'completed'],
            [$vehicleIds[13],'inspection',  'Inspección técnica vehicular',     'Inspectoría Oficial',1500, 16800, '+2 days',  null,        '+92 days', 22000, 'scheduled'],
        ];
        foreach ($maintenance as $m) {
            Database::execute(
                "INSERT INTO maintenance_records (tenant_id, vehicle_id, maintenance_type, description, provider,
                                                  cost, mileage, start_date, end_date, next_due_date, next_due_mileage, status)
                 VALUES (:t,:v,:mt,:d,:p,:c,:mi,:sd,:ed,:nd,:nm,:st)",
                ['t'=>$tid,'v'=>$m[0],'mt'=>$m[1],'d'=>$m[2],'p'=>$m[3],'c'=>$m[4],'mi'=>$m[5],
                 'sd'=>date('Y-m-d', strtotime($m[6])),
                 'ed'=>$m[7] ? date('Y-m-d', strtotime($m[7])) : null,
                 'nd'=>date('Y-m-d', strtotime($m[8])),
                 'nm'=>$m[9],'st'=>$m[10]]
            );
        }

        // --- Incidents ----------------------------------------------------
        $incidents = [
            [$contracts[0], $custIds[3], $vehicleIds[0],  'fuel',           'Cliente devolvió el vehículo con 90% de combustible (entregó 100%). Cobro proporcional.', 1500, 'charged'],
            [$contracts[1], $custIds[2], $vehicleIds[2],  'traffic_fine',   'Multa por exceso de velocidad — Av. Las Américas. Notificada a la Digesett.',                2500, 'open'],
            [null,           $custIds[8], $vehicleIds[5], 'exterior_damage','Rayón ligero en puerta trasera derecha. Cliente reconoce, pendiente evaluación de costo.', 0, 'review'],
        ];
        foreach ($incidents as $i) {
            Database::execute(
                "INSERT INTO incidents (tenant_id, contract_id, customer_id, vehicle_id, type, description, amount, status)
                 VALUES (:t,:ct,:c,:v,:tp,:d,:a,:st)",
                ['t'=>$tid,'ct'=>$i[0],'c'=>$i[1],'v'=>$i[2],'tp'=>$i[3],'d'=>$i[4],'a'=>$i[5],'st'=>$i[6]]
            );
        }

        // --- Notifications ------------------------------------------------
        $notifs = [
            ['Bienvenido a tu demo de Kyros',         'Explora libremente: flotilla, reservas, contratos, pagos, reportes. Esta cuenta y sus datos se eliminarán al expirar el demo.', 'info',    '/admin/dashboard'],
            ['Nueva reserva pública pendiente',       'María Pérez solicitó el Honda Civic (RSV-DEMO-0001). Revisa y confirma.',                                                       'warning', '/admin/reservations'],
            ['Pago recibido — CTR-DEMO-0002',         'Sofía Reyes pagó RD\$ 12,390 vía transferencia BHD.',                                                                            'success', '/admin/payments'],
            ['Mantenimiento programado',              'Hilux Pickup (H500-014) tiene inspección técnica en 2 días.',                                                                    'info',    '/admin/maintenance'],
            ['Multa de tránsito asignada',            'Hyundai Tucson tiene una multa pendiente por exceso de velocidad.',                                                              'warning', '/admin/incidents'],
        ];
        foreach ($notifs as $n) {
            Database::execute(
                "INSERT INTO notifications (tenant_id, title, message, type, action_url, is_read)
                 VALUES (:t,:tt,:m,:tp,:url,0)",
                ['t'=>$tid,'tt'=>$n[0],'m'=>$n[1],'tp'=>$n[2],'url'=>$n[3]]
            );
        }
    }

    /**
     * Sweep expired demo tenants. Returns the number purged.
     * Called opportunistically on login and from cleanup_demos.php.
     */
    public static function sweep(): int
    {
        $rows = Database::select(
            "SELECT id FROM tenants WHERE is_demo = 1 AND demo_expires_at IS NOT NULL AND demo_expires_at <= NOW()"
        );
        $n = 0;
        foreach ($rows as $r) {
            $tid = (int) $r['id'];
            try {
                Database::beginTransaction();
                // FK CASCADE removes users/vehicles/customers/reservations/etc.
                // Non-cascading: activity_logs (tenant_id nullable), login_attempts (no FK), notifications (CASCADE).
                Database::execute("DELETE FROM activity_logs WHERE tenant_id = :t", ['t' => $tid]);
                Database::execute("DELETE FROM tenants WHERE id = :t", ['t' => $tid]);
                Database::commit();
                $n++;
            } catch (\Throwable $e) {
                Database::rollBack();
                Logger::error('Demo sweep failed for tenant ' . $tid . ': ' . $e->getMessage());
            }
        }
        return $n;
    }

    /** Time remaining in seconds for a demo tenant (0 if expired or not a demo). */
    public static function secondsLeft(?array $tenant): int
    {
        if (!$tenant || empty($tenant['is_demo']) || empty($tenant['demo_expires_at'])) return 0;
        $left = strtotime($tenant['demo_expires_at']) - time();
        return max(0, (int) $left);
    }

    protected static function slugify(string $s): string
    {
        $s = strtolower(trim($s));
        $s = preg_replace('/[áàâä]/u','a',$s);
        $s = preg_replace('/[éèêë]/u','e',$s);
        $s = preg_replace('/[íìîï]/u','i',$s);
        $s = preg_replace('/[óòôö]/u','o',$s);
        $s = preg_replace('/[úùûü]/u','u',$s);
        $s = preg_replace('/ñ/u','n',$s);
        $s = preg_replace('/[^a-z0-9]+/','-',$s);
        return trim($s, '-');
    }
}
