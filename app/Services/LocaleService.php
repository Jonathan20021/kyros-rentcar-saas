<?php
namespace App\Services;

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

    /** Currency symbol — kept short for chips/cards. */
    public static function currencySymbol(string $currency): string
    {
        return match (strtoupper($currency)) {
            'COP' => '$',
            'DOP' => 'RD$',
            'USD' => 'US$',
            default => $currency . ' ',
        };
    }

    /** Format a number as money in the tenant's currency. */
    public static function money(float $amount, string $currency = 'DOP'): string
    {
        $symbol = self::currencySymbol($currency);
        // COP rounds to whole pesos; DOP/USD show 2 decimals
        $decimals = strtoupper($currency) === 'COP' ? 0 : 2;
        return $symbol . ' ' . number_format($amount, $decimals, '.', ',');
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
