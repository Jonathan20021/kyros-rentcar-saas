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

    /** Curated starter data so the demo is immediately useful. */
    protected static function seedStarterData(int $tid): void
    {
        // Categories
        $cats = ['Económico','Compacto','Sedán','SUV','Lujo'];
        $catIds = [];
        foreach ($cats as $i => $name) {
            Database::execute(
                "INSERT INTO vehicle_categories (tenant_id, name, slug, icon, status)
                 VALUES (:t, :n, :s, 'car', 'active')",
                ['t' => $tid, 'n' => $name, 's' => self::slugify($name)]
            );
            $catIds[$i] = (int) Database::connection()->lastInsertId();
        }

        // Locations
        Database::execute("INSERT INTO locations (tenant_id, name, address, status) VALUES (:t, 'Sucursal Principal', 'Av. Demo 100', 'active')", ['t' => $tid]);
        $locId = (int) Database::connection()->lastInsertId();

        // Vehicles (5 — enough variety to play)
        $vehicles = [
            ['Toyota','Corolla','LE',2023,'A100-1', 'sedan', 'Blanco', 2200, 10000, 18500, 5, $catIds[2]],
            ['Honda','Civic','Sport',2022,'H200-2', 'sedan', 'Negro', 2400, 10000, 22000, 5, $catIds[2]],
            ['Hyundai','Tucson','GLS',2023,'B300-3', 'suv',   'Plata', 3500, 15000, 12000, 5, $catIds[3]],
            ['Kia','Picanto','GT',2022,'P400-4',   'economico','Rojo',1800, 8000, 26000, 4, $catIds[0]],
            ['Mercedes-Benz','C300','AMG',2023,'L500-5','lujo','Negro', 8500, 30000, 8000, 5, $catIds[4]],
        ];
        $vehicleIds = [];
        foreach ($vehicles as $idx => $v) {
            [$brand,$model,$ver,$year,$plate,$slug,$color,$daily,$dep,$mi,$pax,$catId] = $v;
            Database::execute(
                "INSERT INTO vehicles (tenant_id, brand, model, version, year, plate_number, color, category_id,
                                       transmission, fuel_type, mileage, passengers, doors, daily_price,
                                       deposit_amount, status, location_id, slug, is_public, is_featured)
                 VALUES (:t,:b,:m,:v,:y,:pl,:c,:cat,'automatic','gasoline',:mi,:p,4,:d,:dep,'available',:loc,:s,1, :feat)",
                ['t'=>$tid,'b'=>$brand,'m'=>$model,'v'=>$ver,'y'=>$year,'pl'=>$plate,'c'=>$color,'cat'=>$catId,
                 'mi'=>$mi,'p'=>$pax,'d'=>$daily,'dep'=>$dep,'loc'=>$locId,
                 's'=>self::slugify($brand.'-'.$model.'-'.$ver.'-'.$year),
                 'feat'=>($idx === 4 ? 1 : 0)]
            );
            $vehicleIds[] = (int) Database::connection()->lastInsertId();
        }

        // Extras
        $extras = [
            ['GPS','GPS portátil',200,'per_day'],
            ['Silla de bebé','Silla de seguridad infantil',300,'per_day'],
            ['Conductor adicional','Segundo conductor autorizado',400,'per_reservation'],
            ['Entrega a domicilio','Entregamos donde nos digas',800,'one_time'],
        ];
        foreach ($extras as $ex) {
            Database::execute(
                "INSERT INTO extras (tenant_id, name, description, price, charge_type, status)
                 VALUES (:t, :n, :d, :p, :ct, 'active')",
                ['t'=>$tid,'n'=>$ex[0],'d'=>$ex[1],'p'=>$ex[2],'ct'=>$ex[3]]
            );
        }

        // Customers
        $customers = [
            ['María','Pérez',  'cedula','001-1111111-1','+1 809 555 1001','maria@demo.com'],
            ['Juan', 'Rodríguez','cedula','002-2222222-2','+1 809 555 1002','juan@demo.com'],
            ['Laura','Santos', 'passport','PA00012345',  '+1 809 555 1003','laura@demo.com'],
        ];
        $custIds = [];
        foreach ($customers as $c) {
            Database::execute(
                "INSERT INTO customers (tenant_id, first_name, last_name, document_type, document_number, phone, email, status)
                 VALUES (:t,:f,:l,:dt,:dn,:p,:e,'active')",
                ['t'=>$tid,'f'=>$c[0],'l'=>$c[1],'dt'=>$c[2],'dn'=>$c[3],'p'=>$c[4],'e'=>$c[5]]
            );
            $custIds[] = (int) Database::connection()->lastInsertId();
        }

        // Reservations (one upcoming confirmed, one pending)
        $r1Start = date('Y-m-d', strtotime('+3 days')) . ' 10:00:00';
        $r1End   = date('Y-m-d', strtotime('+6 days')) . ' 10:00:00';
        Database::execute(
            "INSERT INTO reservations (tenant_id, reservation_code, customer_id, vehicle_id, start_datetime, end_datetime,
                                       daily_rate, days_count, subtotal, tax_amount, total_amount, status, source, lead_name, lead_phone)
             VALUES (:t,'RSV-DEMO-0001',:c,:v,:s,:e,2200,3,6600,1188,7788,'confirmed','internal','María Pérez','+1 809 555 1001')",
            ['t'=>$tid,'c'=>$custIds[0],'v'=>$vehicleIds[0],'s'=>$r1Start,'e'=>$r1End]
        );
        $r2Start = date('Y-m-d', strtotime('+10 days')) . ' 09:00:00';
        $r2End   = date('Y-m-d', strtotime('+14 days')) . ' 09:00:00';
        Database::execute(
            "INSERT INTO reservations (tenant_id, reservation_code, customer_id, vehicle_id, start_datetime, end_datetime,
                                       daily_rate, days_count, subtotal, tax_amount, total_amount, status, source, lead_name, lead_phone)
             VALUES (:t,'RSV-DEMO-0002',:c,:v,:s,:e,3500,4,14000,2520,16520,'pending','public','Juan Rodríguez','+1 809 555 1002')",
            ['t'=>$tid,'c'=>$custIds[1],'v'=>$vehicleIds[2],'s'=>$r2Start,'e'=>$r2End]
        );

        // Welcome notification
        Database::execute(
            "INSERT INTO notifications (tenant_id, title, message, type, action_url, is_read)
             VALUES (:t, 'Bienvenido a tu demo de Kyros', 'Explora libremente: flotilla, reservas, contratos, pagos, reportes. Esta cuenta y todos sus datos se eliminarán al expirar el demo.', 'info', '/admin/dashboard', 0)",
            ['t' => $tid]
        );
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
