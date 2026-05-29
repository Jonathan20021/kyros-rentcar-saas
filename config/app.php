<?php
/**
 * Application configuration.
 * Adjust APP_URL / base_path to match your local XAMPP setup.
 */
return [
    'name'        => 'Kyros Rent Car',
    'env'         => 'local',                 // local | production
    'debug'       => true,                     // set false in production
    // Public URL where /public is served. With XAMPP default vhost:
    //   http://localhost/kyros-rentcar-saas/public
    'url'         => 'http://localhost/kyros-rentcar-saas/public',
    // Base path the router strips from the request URI (folder under htdocs + /public).
    'base_path'   => '/kyros-rentcar-saas/public',
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
