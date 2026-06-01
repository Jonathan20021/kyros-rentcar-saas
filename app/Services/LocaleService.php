<?php
namespace App\Services;

use App\Core\Auth;
use App\Core\Config;
use App\Core\Database;

/**
 * LocaleService — single source of truth for country-specific behavior.
 *
 * Drives:
 *   - Tax labels & default rates (ITBIS 18 % vs IVA 19 %)
 *   - Currency formatting (DOP vs COP)
 *   - Customer document types
 *   - Vehicle expiration labels (marbete/inspección vs SOAT/tecnomecánica)
 *   - Tax-ID label (RNC vs NIT)
 *   - Phone country code
 *   - License-plate restriction concept ("Pico y placa" for CO)
 *
 * Use `forTenant($tenant)` everywhere instead of hardcoding labels.
 */
class LocaleService
{
    public const COUNTRIES = ['DO', 'CO'];

    /**
     * Currency registry — code => [symbol, name (es), decimals].
     * Prices are stored as plain numbers and interpreted in the tenant's
     * currency; switching currency re-labels/re-formats, it does NOT convert
     * amounts (no FX rates). Zero-decimal currencies (JPY, CLP, COP, …) drop
     * the decimal places automatically.
     */
    public const CURRENCIES = [
        'DOP' => ['RD$',  'Peso dominicano',        2],
        'USD' => ['US$',  'Dólar estadounidense',   2],
        'EUR' => ['€',    'Euro',                   2],
        'COP' => ['$',    'Peso colombiano',        0],
        'MXN' => ['$',    'Peso mexicano',          2],
        'ARS' => ['$',    'Peso argentino',         2],
        'CLP' => ['$',    'Peso chileno',           0],
        'PEN' => ['S/',   'Sol peruano',            2],
        'BRL' => ['R$',   'Real brasileño',         2],
        'GTQ' => ['Q',    'Quetzal guatemalteco',   2],
        'CRC' => ['₡',    'Colón costarricense',    2],
        'PAB' => ['B/.',  'Balboa panameño',        2],
        'UYU' => ['$U',   'Peso uruguayo',          2],
        'PYG' => ['₲',    'Guaraní paraguayo',      0],
        'BOB' => ['Bs',   'Boliviano',              2],
        'VES' => ['Bs.',  'Bolívar venezolano',     2],
        'HNL' => ['L',    'Lempira hondureño',      2],
        'NIO' => ['C$',   'Córdoba nicaragüense',   2],
        'CUP' => ['$',    'Peso cubano',            2],
        'HTG' => ['G',    'Gourde haitiano',        2],
        'JMD' => ['J$',   'Dólar jamaiquino',       2],
        'TTD' => ['TT$',  'Dólar de Trinidad',      2],
        'XCD' => ['EC$',  'Dólar del Caribe Este',  2],
        'BBD' => ['Bds$', 'Dólar de Barbados',      2],
        'BSD' => ['B$',   'Dólar bahameño',         2],
        'BZD' => ['BZ$',  'Dólar beliceño',         2],
        'AWG' => ['ƒ',    'Florín arubeño',         2],
        'ANG' => ['ƒ',    'Florín antillano',       2],
        'KYD' => ['CI$',  'Dólar de Caimán',        2],
        'GYD' => ['G$',   'Dólar guyanés',          2],
        'SRD' => ['$',    'Dólar surinamés',        2],
        'GBP' => ['£',    'Libra esterlina',        2],
        'CAD' => ['C$',   'Dólar canadiense',       2],
        'CHF' => ['CHF',  'Franco suizo',           2],
        'AUD' => ['A$',   'Dólar australiano',      2],
        'NZD' => ['NZ$',  'Dólar neozelandés',      2],
        'JPY' => ['¥',    'Yen japonés',            0],
        'CNY' => ['¥',    'Yuan chino',             2],
        'HKD' => ['HK$',  'Dólar de Hong Kong',     2],
        'SGD' => ['S$',   'Dólar de Singapur',      2],
        'KRW' => ['₩',    'Won surcoreano',         0],
        'INR' => ['₹',    'Rupia india',            2],
        'IDR' => ['Rp',   'Rupia indonesia',        0],
        'MYR' => ['RM',   'Ringgit malayo',         2],
        'PHP' => ['₱',    'Peso filipino',          2],
        'THB' => ['฿',    'Baht tailandés',         2],
        'TWD' => ['NT$',  'Dólar taiwanés',         2],
        'VND' => ['₫',    'Dong vietnamita',        0],
        'AED' => ['د.إ',  'Dírham (EAU)',           2],
        'SAR' => ['﷼',    'Riyal saudí',            2],
        'QAR' => ['﷼',    'Riyal catarí',           2],
        'ILS' => ['₪',    'Séquel israelí',         2],
        'TRY' => ['₺',    'Lira turca',             2],
        'EGP' => ['£',    'Libra egipcia',          2],
        'MAD' => ['DH',   'Dírham marroquí',        2],
        'ZAR' => ['R',    'Rand sudafricano',       2],
        'NGN' => ['₦',    'Naira nigeriana',        2],
        'XAF' => ['FCFA', 'Franco CFA (BEAC)',      0],
        'XOF' => ['CFA',  'Franco CFA (BCEAO)',     0],
        'RUB' => ['₽',    'Rublo ruso',             2],
        'UAH' => ['₴',    'Grivna ucraniana',       2],
        'PLN' => ['zł',   'Esloti polaco',          2],
        'CZK' => ['Kč',   'Corona checa',           2],
        'HUF' => ['Ft',   'Florín húngaro',         2],
        'RON' => ['lei',  'Leu rumano',             2],
        'SEK' => ['kr',   'Corona sueca',           2],
        'NOK' => ['kr',   'Corona noruega',         2],
        'DKK' => ['kr',   'Corona danesa',          2],
        'ISK' => ['kr',   'Corona islandesa',       0],
    ];

    /** Codes surfaced first in the picker (region-relevant + majors). */
    public const POPULAR_CURRENCIES = ['DOP', 'USD', 'EUR', 'COP', 'MXN', 'ARS', 'CLP', 'PEN', 'BRL', 'GTQ', 'CRC', 'PAB'];

    /** Per-request active currency for the global money() helper (see currentCurrency()). */
    protected static ?string $currentCurrency = null;
    /** Cached currency of the authenticated tenant (lazy fallback). */
    protected static ?string $authCurrency = null;

    /** Defaults for a tenant when they pick a country in settings. */
    public static function defaultsFor(string $country): array
    {
        if ($country === 'CO') {
            return [
                'country'      => 'CO',
                'currency'     => 'COP',
                'tax_rate'     => 19.00,
                'tax_label'    => 'IVA',
                'tax_id_label' => 'NIT',
            ];
        }
        // Dominican Republic default
        return [
            'country'      => 'DO',
            'currency'     => 'DOP',
            'tax_rate'     => 18.00,
            'tax_label'    => 'ITBIS',
            'tax_id_label' => 'RNC',
        ];
    }

    /** Country name in Spanish for the UI. */
    public static function countryName(string $country): string
    {
        return match ($country) {
            'CO' => 'Colombia',
            'DO' => 'República Dominicana',
            default => $country,
        };
    }

    /** Customer document types appropriate for the tenant's country. */
    public static function customerDocTypes(string $country): array
    {
        if ($country === 'CO') {
            return [
                'cedula'              => 'Cédula de ciudadanía',
                'cedula_extranjeria'  => 'Cédula de extranjería',
                'passport'            => 'Pasaporte',
                'nit'                 => 'NIT (empresa)',
            ];
        }
        return [
            'cedula'   => 'Cédula',
            'passport' => 'Pasaporte',
            'license'  => 'Licencia de conducir',
            'rnc'      => 'RNC (empresa)',
        ];
    }

    /**
     * Vehicle expiration labels + the matching DB column name. The frontend
     * loops over this to render the right fields per country.
     */
    public static function vehicleExpirationFields(string $country): array
    {
        if ($country === 'CO') {
            return [
                ['col' => 'soat_expires',          'label' => 'SOAT',           'icon' => 'shield-check', 'critical' => true],
                ['col' => 'tecnomecanica_expires', 'label' => 'Tecnomecánica',  'icon' => 'wrench',       'critical' => true],
                ['col' => 'plate_expires',         'label' => 'Placa',          'icon' => 'badge',        'critical' => false],
                ['col' => 'insurance_expires',     'label' => 'Seguro',         'icon' => 'shield',       'critical' => false],
            ];
        }
        return [
            ['col' => 'marbete_expires',     'label' => 'Marbete',           'icon' => 'sticker',       'critical' => true],
            ['col' => 'inspection_expires',  'label' => 'Inspección técnica','icon' => 'clipboard-check','critical' => true],
            ['col' => 'plate_expires',       'label' => 'Placa',             'icon' => 'badge',         'critical' => false],
            ['col' => 'insurance_expires',   'label' => 'Seguro',            'icon' => 'shield',        'critical' => false],
        ];
    }

    /** Registry row for a currency, falling back to a generic 2-decimal entry. */
    public static function currencyMeta(string $currency): array
    {
        $code = strtoupper(trim($currency));
        if (isset(self::CURRENCIES[$code])) {
            [$symbol, $name, $decimals] = self::CURRENCIES[$code];
            return ['code' => $code, 'symbol' => $symbol, 'name' => $name, 'decimals' => $decimals];
        }
        return ['code' => $code ?: 'USD', 'symbol' => ($code ?: 'USD') . ' ', 'name' => $code, 'decimals' => 2];
    }

    /** Currency symbol — kept short for chips/cards. */
    public static function currencySymbol(string $currency): string
    {
        return self::currencyMeta($currency)['symbol'];
    }

    /** Number of decimal places a currency shows (0 for JPY/CLP/COP/…). */
    public static function currencyDecimals(string $currency): int
    {
        return self::currencyMeta($currency)['decimals'];
    }

    /** Human currency name (es). */
    public static function currencyName(string $currency): string
    {
        return self::currencyMeta($currency)['name'];
    }

    /** Format a number as money in the given currency (no FX conversion). */
    public static function money(float $amount, string $currency = 'DOP'): string
    {
        $meta = self::currencyMeta($currency);
        return $meta['symbol'] . ' ' . number_format($amount, $meta['decimals'], '.', ',');
    }

    /**
     * Options for a currency <select>/combobox: popular codes first, then the
     * rest alphabetically by code. Each row: [code, name, symbol, decimals, group].
     */
    public static function currencyOptions(): array
    {
        $rows = [];
        foreach (self::POPULAR_CURRENCIES as $code) {
            if (!isset(self::CURRENCIES[$code])) continue;
            [$symbol, $name, $dec] = self::CURRENCIES[$code];
            $rows[] = ['code' => $code, 'name' => $name, 'symbol' => $symbol, 'decimals' => $dec, 'group' => 'Frecuentes'];
        }
        $rest = array_diff(array_keys(self::CURRENCIES), self::POPULAR_CURRENCIES);
        sort($rest);
        foreach ($rest as $code) {
            [$symbol, $name, $dec] = self::CURRENCIES[$code];
            $rows[] = ['code' => $code, 'name' => $name, 'symbol' => $symbol, 'decimals' => $dec, 'group' => 'Todas las monedas'];
        }
        return $rows;
    }

    /** Explicitly set the active currency for the global money() helper this request. */
    public static function setCurrentCurrency(?string $currency): void
    {
        $code = strtoupper(trim((string) $currency));
        self::$currentCurrency = $code !== '' ? $code : null;
    }

    /**
     * The active currency for global money()/money_compact(). Resolution order:
     *   1. an explicitly set currency (admin views, storefront tenant),
     *   2. the authenticated tenant's currency (covers PDFs/receipts that bypass
     *      renderAdmin),
     *   3. the platform default from config.
     */
    public static function currentCurrency(): string
    {
        if (self::$currentCurrency !== null) return self::$currentCurrency;
        $tid = Auth::tenantId();
        if ($tid) {
            if (self::$authCurrency === null) {
                $cur = Database::scalar("SELECT currency FROM tenants WHERE id = :id", ['id' => $tid]);
                self::$authCurrency = $cur ? strtoupper((string) $cur) : '';
            }
            if (self::$authCurrency !== '') return self::$authCurrency;
        }
        return strtoupper((string) Config::get('app.currency', 'DOP'));
    }

    /** Phone country code + sample mask (for placeholders). */
    public static function phoneHint(string $country): array
    {
        return $country === 'CO'
            ? ['code' => '+57',  'sample' => '+57 300 555 1234']
            : ['code' => '+1',   'sample' => '+1 809 555 0000'];
    }

    /**
     * Country-specific compliance flags surfaced in the UI:
     *   - DO → NCF required for legal invoicing
     *   - CO → DIAN resolution + SOAT mandatory
     */
    public static function complianceFor(string $country): array
    {
        if ($country === 'CO') {
            return [
                'invoice_field'    => 'DIAN',
                'invoice_help'     => 'Resolución DIAN para facturación electrónica.',
                'mandatory_doc'    => 'SOAT (seguro obligatorio)',
                'plate_restrict'   => 'Pico y placa por ciudad',
            ];
        }
        return [
            'invoice_field'    => 'NCF',
            'invoice_help'     => 'Número de Comprobante Fiscal (DGII). B02 para consumidor final, B01 para crédito fiscal.',
            'mandatory_doc'    => 'Marbete vigente',
            'plate_restrict'   => null,
        ];
    }

    /** Snapshot of every locale piece for views — pass the tenant once. */
    public static function forTenant(array $tenant): array
    {
        $country = strtoupper($tenant['country'] ?? 'DO');
        if (!in_array($country, self::COUNTRIES, true)) $country = 'DO';
        return [
            'country'        => $country,
            'country_name'   => self::countryName($country),
            'currency'       => $tenant['currency'] ?? 'DOP',
            'currency_symbol'=> self::currencySymbol($tenant['currency'] ?? 'DOP'),
            'tax_label'      => $tenant['tax_label'] ?? ($country === 'CO' ? 'IVA' : 'ITBIS'),
            'tax_rate'       => (float)($tenant['tax_rate'] ?? ($country === 'CO' ? 19 : 18)),
            'tax_id_label'   => $tenant['tax_id_label'] ?? ($country === 'CO' ? 'NIT' : 'RNC'),
            'doc_types'      => self::customerDocTypes($country),
            'expirations'    => self::vehicleExpirationFields($country),
            'phone'          => self::phoneHint($country),
            'compliance'     => self::complianceFor($country),
        ];
    }
}
