<?php
namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Auth;
use App\Core\Session;
use App\Core\Database;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Plan;
use App\Models\ActivityLog;
use App\Services\LoginThrottle;
use App\Services\DemoService;
use App\Models\DemoLicense;

class AuthController extends Controller
{
    // ---- LOGIN ----------------------------------------------------------
    public function showLogin(Request $request): void
    {
        if (Auth::check()) { $this->redirect('/dashboard'); }
        // Opportunistic cleanup: purge any expired demo tenants before we render.
        try { DemoService::sweep(); } catch (\Throwable $e) { /* non-fatal */ }
        $this->view('auth/login', [
            'title'       => 'Iniciar sesión · Kyros Rent Car',
            'demoOffers'  => DemoLicense::publicOffers(),
        ], 'auth');
    }

    public function login(Request $request): void
    {
        $email = strtolower($request->str('email'));
        $pass  = (string) $request->input('password', '');
        $ip    = $request->ip();

        $data = $this->validateOrBack(
            ['email' => $email, 'password' => $pass],
            ['email' => 'required|email|max:150', 'password' => 'required|min:1'],
            '/login'
        );

        if (LoginThrottle::tooManyAttempts($email, $ip)) {
            $secs = LoginThrottle::secondsRemaining($email, $ip);
            Session::flash('error', 'Demasiados intentos fallidos. Intenta de nuevo en ' . ceil($secs / 60) . ' min.');
            $this->redirect('/login');
        }

        if (Auth::attempt($email, $pass)) {
            // Block tenant users whose company is still in approval queue.
            // Super admins (no tenant) and demo tenants are allowed through.
            $tid = Auth::tenantId();
            if ($tid) {
                $tStatus = Database::scalar("SELECT status FROM tenants WHERE id = :t", ['t' => $tid]);
                if ($tStatus === 'pending_approval') {
                    Auth::logout();
                    Session::flash('error', 'Tu cuenta está en revisión. Te avisaremos cuando esté lista.');
                    $this->redirect('/login');
                }
                if ($tStatus === 'suspended' || $tStatus === 'inactive') {
                    Auth::logout();
                    Session::flash('error', 'Tu cuenta está suspendida. Contacta a soporte.');
                    $this->redirect('/login');
                }
            }
            LoginThrottle::clear($email, $ip);
            LoginThrottle::record($email, $ip, true);
            ActivityLog::record('login', 'auth', Auth::id(), 'Inicio de sesion');
            // Platform alert: login success
            try {
                $u = Auth::user() ?: ['email' => $email];
                \App\Services\NotificationService::notifyLogin(
                    ['name' => $u['name'] ?? '', 'email' => $u['email'] ?? $email, 'tenant_id' => $u['tenant_id'] ?? null,
                     'tenant_name' => $u['tenant_name'] ?? null],
                    $ip, $_SERVER['HTTP_USER_AGENT'] ?? null, true
                );
            } catch (\Throwable $e) { \App\Core\Logger::error('notify login ok: ' . $e->getMessage()); }
            $intended = Session::get('_intended');
            Session::forget('_intended');
            $this->redirect($intended ?: '/dashboard');
        }

        LoginThrottle::record($email, $ip, false);
        // Platform alert: failed login
        try {
            \App\Services\NotificationService::notifyLogin(
                ['name' => '', 'email' => $email, 'tenant_id' => null],
                $ip, $_SERVER['HTTP_USER_AGENT'] ?? null, false
            );
        } catch (\Throwable $e) { \App\Core\Logger::error('notify login fail: ' . $e->getMessage()); }
        Session::flash('error', 'Credenciales invalidas. Verifica tu correo y contrasena.');
        Session::flashInput(['email' => $email]);
        $this->redirect('/login');
    }

    /** Routes a logged-in user to the correct panel. */
    public function home(Request $request): void
    {
        if (Auth::isSuperAdmin()) {
            $this->redirect('/super-admin');
        }
        $this->redirect('/admin/dashboard');
    }

    public function logout(Request $request): void
    {
        if (Auth::check()) {
            ActivityLog::record('logout', 'auth', Auth::id(), 'Cierre de sesion');
        }
        Auth::logout();
        Session::flash('success', 'Sesion cerrada correctamente.');
        $this->redirect('/login');
    }

    // ---- DEMO LICENSE LOGIN --------------------------------------------
    public function demo(Request $request): void
    {
        $code  = trim((string) $request->str('demo_code'));
        $name  = trim((string) $request->str('demo_name')) ?: 'Demo User';
        $email = strtolower(trim((string) $request->str('demo_email')));

        if ($code === '') {
            Session::flash('error', 'Indica un código de licencia.');
            $this->redirect('/login');
        }

        $license = DemoLicense::findActiveByCode($code);
        if (!$license || !DemoLicense::isUsable($license)) {
            Session::flash('error', 'Código de licencia inválido o agotado.');
            $this->redirect('/login');
        }

        // Generate a unique demo email if user did not provide one to avoid collisions.
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = 'demo-' . substr(bin2hex(random_bytes(4)), 0, 8) . '@kyros.local';
        }
        $email = self::uniqueEmail($email);

        $password = 'Demo' . bin2hex(random_bytes(3));

        try {
            [$tid, $uid, $slug] = DemoService::redeem($license, $name, $email, $password);
        } catch (\Throwable $e) {
            Session::flash('error', 'No se pudo crear la demo: ' . $e->getMessage());
            $this->redirect('/login');
        }

        // Auto-login as owner of the new demo tenant.
        $user = User::findByEmail($email, $tid);
        $user['role_slug'] = 'owner';
        Auth::login($user);
        ActivityLog::record('login', 'auth', $uid, 'Demo redimida: ' . $license['code']);

        // Platform alert: demo was redeemed.
        try {
            \App\Services\NotificationService::notifyDemoCreated(
                ['name' => $name, 'slug' => $slug, 'demo_expires_at' => date('Y-m-d H:i:s', time() + ((int)$license['hours_valid']) * 3600)],
                ['name' => $name, 'email' => $email],
                $license
            );
        } catch (\Throwable $e) { \App\Core\Logger::error('notify demo: ' . $e->getMessage()); }

        Session::flash('success', sprintf(
            '¡Demo activa! Tienes %d horas para explorar como %s. Tu página pública: /r/%s',
            (int) $license['hours_valid'], $license['plan_name'], $slug
        ));
        $this->redirect('/admin/dashboard');
    }

    /** Returns an email that doesn't collide with an existing user. */
    protected static function uniqueEmail(string $email): string
    {
        if (!Database::scalar("SELECT 1 FROM users WHERE email = :e LIMIT 1", ['e' => $email])) {
            return $email;
        }
        [$local, $domain] = explode('@', $email, 2);
        do {
            $cand = $local . '+' . substr(bin2hex(random_bytes(3)), 0, 6) . '@' . $domain;
        } while (Database::scalar("SELECT 1 FROM users WHERE email = :e LIMIT 1", ['e' => $cand]));
        return $cand;
    }

    // ---- REGISTER (rent car company) -----------------------------------
    public function showRegister(Request $request): void
    {
        if (Auth::check()) { $this->redirect('/dashboard'); }
        // Plan picked on the pricing page (?plan=slug). On a failed-validation
        // bounce the query string is gone, so fall back to the flashed old input.
        $slug = $request->str('plan') ?: (string) Session::old('plan');
        $this->view('auth/register', [
            'title' => 'Crear mi rent car · Kyros Rent Car',
            'plan'  => Plan::resolvePublic($slug ?: null),
        ], 'auth');
    }

    public function register(Request $request): void
    {
        $input = $request->only(['company','email','phone','password','password_confirmation','owner_name','plan']);

        $data = $this->validateOrBack(
            $input,
            [
                'company'  => 'required|min:3|max:150',
                'owner_name' => 'required|min:3|max:120',
                'email'    => 'required|email|max:150',
                'phone'    => 'max:30',
                'password' => 'required|min:8|confirmed',
            ],
            '/register'
        );

        $email = strtolower($data['email']);

        // Email must be unique among super-admins is not relevant; for tenants check global staff uniqueness per tenant.
        if (User::findByEmail($email, null) || Database::scalar("SELECT COUNT(*) FROM users WHERE email = :e", ['e' => $email])) {
            Session::flash('error', 'Ese correo ya esta registrado.');
            Session::flashInput($input);
            $this->redirect('/register');
        }

        // Plan the visitor chose on the pricing page; falls back to the cheapest
        // public plan if missing or invalid.
        $plan = Plan::resolvePublic($input['plan'] ?? null);

        try {
            Database::beginTransaction();

            $slug = Tenant::uniqueSlug($data['company']);
            $tenantId = Tenant::create([
                'name'           => $data['company'],
                'slug'           => $slug,
                'email'          => $email,
                'phone'          => $data['phone'] ?? null,
                'whatsapp'       => $data['phone'] ?? null,
                'primary_color'  => '#4F46E5',
                'secondary_color'=> '#06B6D4',
                'currency'       => 'DOP',
                'plan_id'        => (int) ($plan['id'] ?? 1),
                // Fresh registrations sit in `pending_approval` until super admin
                // reviews. No free trial here — trials are handled exclusively by
                // the demo system (DemoService::redeem).
                'status'         => 'pending_approval',
                'trial_ends_at'  => null,
            ]);

            // Owner role = 2
            $userId = User::create([
                'tenant_id' => $tenantId,
                'role_id'   => 2,
                'name'      => $data['owner_name'],
                'email'     => $email,
                'password'  => password_hash($data['password'], PASSWORD_BCRYPT),
                'phone'     => $data['phone'] ?? null,
                'status'    => 'active',
                'email_verified_at' => date('Y-m-d H:i:s'),
            ]);

            // Seed default categories for the new tenant
            foreach (['Economico','Sedan','SUV','Lujo'] as $cat) {
                Database::execute(
                    "INSERT INTO vehicle_categories (tenant_id, name, slug, status) VALUES (:t,:n,:s,'active')",
                    ['t' => $tenantId, 'n' => $cat, 's' => slugify($cat)]
                );
            }

            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            \App\Core\Logger::error('Register failed: ' . $e->getMessage());
            Session::flash('error', 'No se pudo crear la empresa. Intenta de nuevo.');
            $this->redirect('/register');
        }

        // Welcome email (Resend; silently falls back to log if disabled)
        try {
            $body = '<p>Hola <strong>' . e($data['owner_name']) . '</strong>,</p>'
                . '<p>Tu rent car <strong>' . e($data['company']) . '</strong> ya está creada en Kyros. '
                . 'Tu página pública de reservas está disponible en:</p>'
                . '<p><a href="' . abs_url('/r/' . $slug) . '">' . abs_url('/r/' . $slug) . '</a></p>'
                . '<p>Entra a tu panel para cargar tu flotilla y empezar a recibir reservas.</p>';
            \App\Services\Mailer::send($email, 'Bienvenido a Kyros Rent Car',
                \App\Services\Mailer::layout('¡Tu rent car está lista! 🚗', $body, null, ['label'=>'Ir a mi panel','url'=>abs_url('/login')]));
        } catch (\Throwable $e) { \App\Core\Logger::error('welcome mail: ' . $e->getMessage()); }

        // Platform alert to ops inbox (Super Admin configured recipients).
        try {
            \App\Services\NotificationService::notifyRegistration(
                [
                    'name'      => $data['company'],
                    'slug'      => $slug,
                    'email'     => $email,
                    'phone'     => $data['phone'] ?? null,
                    'plan_name' => $plan['name'] ?? 'Starter',
                    'status'    => 'pending_approval',
                ],
                ['name' => $data['owner_name'], 'email' => $email]
            );
        } catch (\Throwable $e) { \App\Core\Logger::error('notify registration: ' . $e->getMessage()); }

        // No auto-login — wait for super admin to activate the tenant.
        // The login flow checks tenant status and blocks pending_approval.
        Session::flash('success', '¡Cuenta creada! Estamos revisando tu solicitud. Te avisaremos por email cuando esté activa.');
        $this->redirect('/login');
    }

    // ---- FORGOT / RESET -------------------------------------------------
    public function showForgot(Request $request): void
    {
        $this->view('auth/forgot', ['title' => 'Recuperar contrasena · Kyros Rent Car'], 'auth');
    }

    public function forgot(Request $request): void
    {
        $email = strtolower($request->str('email'));
        $this->validateOrBack(['email' => $email], ['email' => 'required|email'], '/forgot-password');

        // Always respond the same way to avoid user enumeration.
        $user = Database::selectOne("SELECT id FROM users WHERE email = :e AND deleted_at IS NULL LIMIT 1", ['e' => $email]);
        if ($user) {
            $token = bin2hex(random_bytes(32));
            Database::execute(
                "UPDATE users SET reset_token = :t, reset_expires_at = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = :id",
                ['t' => hash('sha256', $token), 'id' => $user['id']]
            );
            $link = abs_url('/reset-password/' . $token);
            \App\Core\Logger::info('Password reset link: ' . $link . ' (email ' . $email . ')');
            try {
                $body = '<p>Recibimos una solicitud para restablecer tu contraseña.</p>'
                    . '<p>El enlace expira en 1 hora. Si no fuiste tú, ignora este correo.</p>';
                \App\Services\Mailer::send($email, 'Restablece tu contraseña · Kyros Rent Car',
                    \App\Services\Mailer::layout('Restablecer contraseña', $body, null, ['label'=>'Crear nueva contraseña','url'=>$link]));
            } catch (\Throwable $e) { \App\Core\Logger::error('reset mail: ' . $e->getMessage()); }
        }
        Session::flash('success', 'Si el correo existe, te enviamos un enlace de recuperacion.');
        $this->redirect('/login');
    }

    public function showReset(Request $request, string $token): void
    {
        $this->view('auth/reset', ['title' => 'Nueva contrasena · Kyros Rent Car', 'token' => $token], 'auth');
    }

    public function reset(Request $request): void
    {
        $token = (string) $request->input('token', '');
        $data = $this->validateOrBack(
            $request->only(['password','password_confirmation','token']),
            ['password' => 'required|min:8|confirmed'],
            '/reset-password/' . $token
        );

        $hashed = hash('sha256', $token);
        $user = Database::selectOne(
            "SELECT id FROM users WHERE reset_token = :t AND reset_expires_at > NOW() LIMIT 1",
            ['t' => $hashed]
        );
        if (!$user) {
            Session::flash('error', 'El enlace de recuperacion es invalido o expiro.');
            $this->redirect('/forgot-password');
        }

        Database::execute(
            "UPDATE users SET password = :p, reset_token = NULL, reset_expires_at = NULL WHERE id = :id",
            ['p' => password_hash($data['password'], PASSWORD_BCRYPT), 'id' => $user['id']]
        );
        Session::flash('success', 'Contrasena actualizada. Ya puedes iniciar sesion.');
        $this->redirect('/login');
    }
}
