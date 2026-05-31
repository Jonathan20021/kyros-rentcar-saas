<?php
$code    = '500';
$title   = 'Algo salió mal';
$message = $message ?? 'Tuvimos un problema procesando tu solicitud. Nuestro equipo ya fue notificado. Intenta de nuevo en unos segundos.';
$icon    = 'alert-octagon';
$tone    = 'red';
include __DIR__ . '/_layout.php';
