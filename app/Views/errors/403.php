<?php
$code    = '403';
$title   = 'Acceso denegado';
$message = $message ?? 'No tienes permiso para ver esta página. Si crees que es un error, contacta al administrador de tu empresa.';
$icon    = 'shield-x';
$tone    = 'amber';
$backUrl = url('/dashboard');
include __DIR__ . '/_layout.php';
