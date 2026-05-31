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
            'title'       => 'Kyros Rent Car - El sistema operativo de tu rent car',
            'plans'       => Plan::publicPlans(),
            'demoOffers'  => DemoLicense::publicOffers(),
        ], 'marketing');
    }

    public function plans(Request $request): void
    {
        $this->view('public/plans', [
            'title' => 'Planes - Kyros Rent Car',
            'plans' => Plan::publicPlans(),
        ], 'marketing');
    }

    public function page(Request $request, string $slug): void
    {
        $pages = [
            'producto' => [
                'title' => 'Producto - Kyros Rent Car',
                'eyebrow' => 'Producto',
                'headline' => 'Todo el ciclo de una rent car en una sola operacion.',
                'intro' => 'Kyros conecta flotilla, reservas, contratos, pagos, documentos, promociones y pagina publica para que el equipo trabaje con menos pasos manuales.',
                'sections' => [
                    ['Flotilla y disponibilidad', 'Controla estados, vencimientos, mantenimientos, sucursales, precios y fotos publicas de cada unidad.'],
                    ['Reservas y contratos', 'Convierte solicitudes publicas en reservas internas, contratos firmados y documentos listos para PDF.'],
                    ['Finanzas operativas', 'Registra pagos, facturas, gastos, cierres de caja y reportes de rentabilidad por vehiculo.'],
                    ['Pagina publica tenant', 'Cada rent car tiene una vitrina propia con marca, colores, WhatsApp, catalogo y flujo de reserva.'],
                ],
            ],
            'seguridad' => [
                'title' => 'Seguridad - Kyros Rent Car',
                'eyebrow' => 'Seguridad',
                'headline' => 'Aislamiento por empresa, controles de acceso y cargas protegidas.',
                'intro' => 'La plataforma esta disenada para separar los datos de cada tenant y reducir riesgos comunes en operaciones con documentos, pagos y firmas.',
                'sections' => [
                    ['Multi-tenant aislado', 'Las consultas del panel se ejecutan con alcance de tenant para evitar cruces de informacion entre empresas.'],
                    ['Roles y permisos', 'Cada modulo critico se protege con permisos de lectura, creacion, edicion, eliminacion o gestion.'],
                    ['Proteccion de formularios', 'Las acciones POST usan CSRF y validacion de entrada antes de modificar datos.'],
                    ['Uploads controlados', 'Las imagenes y documentos pasan por validacion MIME, limites de peso, nombres aleatorios y cuota de almacenamiento.'],
                ],
            ],
            'privacidad' => [
                'title' => 'Privacidad - Kyros Rent Car',
                'eyebrow' => 'Privacidad',
                'headline' => 'Tus datos operativos pertenecen a tu empresa.',
                'intro' => 'Esta politica resume como Kyros trata la informacion usada para operar rent cars: clientes, vehiculos, reservas, contratos, pagos y configuracion de marca.',
                'sections' => [
                    ['Datos que guardamos', 'Informacion de cuenta, datos de tenant, clientes, vehiculos, reservas, contratos, pagos y archivos subidos por usuarios autorizados.'],
                    ['Uso de la informacion', 'Usamos los datos para prestar el servicio, generar documentos, enviar notificaciones, mantener seguridad y mejorar la plataforma.'],
                    ['Acceso interno', 'Los usuarios ven la informacion segun su rol y tenant. El equipo Kyros puede revisar datos solo para soporte, seguridad o administracion del servicio.'],
                    ['Retencion', 'Conservamos informacion mientras la cuenta este activa o sea necesaria para obligaciones operativas, legales o de soporte.'],
                ],
            ],
            'terminos' => [
                'title' => 'Terminos - Kyros Rent Car',
                'eyebrow' => 'Terminos',
                'headline' => 'Condiciones claras para usar Kyros Rent Car.',
                'intro' => 'Al usar Kyros, aceptas operar la plataforma de forma licita, mantener la veracidad de tus datos y proteger el acceso de tu equipo.',
                'sections' => [
                    ['Uso permitido', 'La plataforma debe usarse para administrar operaciones reales de renta de vehiculos y servicios relacionados.'],
                    ['Responsabilidad del tenant', 'Cada empresa es responsable de sus precios, contratos, politicas comerciales, cobros, impuestos y cumplimiento local.'],
                    ['Disponibilidad', 'Trabajamos para mantener el servicio estable, pero pueden existir ventanas de mantenimiento, incidentes o limitaciones de terceros.'],
                    ['Cambios del servicio', 'Kyros puede mejorar modulos, cambiar planes o actualizar condiciones avisando por los canales disponibles cuando aplique.'],
                ],
            ],
            'contacto' => [
                'title' => 'Contacto - Kyros Rent Car',
                'eyebrow' => 'Contacto',
                'headline' => 'Hablemos de tu operacion de renta.',
                'intro' => 'Si quieres activar una cuenta, revisar un plan o adaptar Kyros a tu flujo de trabajo, escribe al equipo de soporte.',
                'sections' => [
                    ['Email', 'soporte@kyrosrd.com'],
                    ['Ubicacion', 'Santo Domingo, Republica Dominicana.'],
                    ['Soporte', 'Atendemos solicitudes de activacion, configuracion, storage, demos y preguntas del panel.'],
                    ['Demo', 'Puedes crear una demo temporal desde el login para probar la plataforma con datos de ejemplo.'],
                ],
            ],
        ];

        if (!isset($pages[$slug])) {
            $this->abort(404, 'Pagina no encontrada.');
        }

        $this->view('public/page', [
            'title' => $pages[$slug]['title'],
            'page' => $pages[$slug],
        ], 'marketing');
    }

    public function product(Request $request): void { $this->page($request, 'producto'); }
    public function security(Request $request): void { $this->page($request, 'seguridad'); }
    public function privacy(Request $request): void { $this->page($request, 'privacidad'); }
    public function terms(Request $request): void { $this->page($request, 'terminos'); }
    public function contact(Request $request): void { $this->page($request, 'contacto'); }
}
