<?php
/**
 * Application configuration.
 *
 * The SaaS lives at https://rentcar.kyrosrd.com in production.
 * In local XAMPP dev it's served from http://localhost/kyros-rentcar-saas/public.
 *
 * Set the KYROS_ENV environment variable (or edit `env` below) to switch
 * between 'local' and 'production'.
 */
$env = getenv('KYROS_ENV') ?: 'local';

$production = [
    'url'       => 'https://rentcar.kyrosrd.com',
    'base_path' => '',
];
$local = [
    'url'       => 'http://localhost/kyros-rentcar-saas/public',
    'base_path' => '/kyros-rentcar-saas/public',
];
$urls = $env === 'production' ? $production : $local;

return [
    'name'        => 'Kyros Rent Car',
    'short_name'  => 'Kyros Rent Car',
    'tagline'     => 'El sistema operativo de tu rent car',
    'env'         => $env,
    'debug'       => $env !== 'production',
    'url'         => $urls['url'],
    'base_path'   => $urls['base_path'],
    'timezone'    => 'America/Santo_Domingo',
    'locale'      => 'es',
    'currency'    => 'DOP',
    'currency_symbol' => 'RD$',

    // Absolute filesystem paths (do not edit unless you move folders)
    'root_path'    => dirname(__DIR__),
    'storage_path' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage',
    'upload_path'  => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'uploads',
    'upload_url'   => '/assets/uploads',
];
