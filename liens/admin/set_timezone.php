<?php
session_start();
header('Access-Control-Allow-Origin: https://lien.cat'); // Remplacez
header('Access-Control-Allow-Credentials: true');

$validZones = DateTimeZone::listIdentifiers();
$timezone = $_POST['timezone'] ?? 'UTC';

if(in_array($timezone, $validZones)) {
    $_SESSION['user_timezone'] = $timezone;
    echo "OK : " . $timezone . " a été enregistré\n";
    exit;
}

http_response_code(400);
echo "Erreur : Fuseau invalide";
