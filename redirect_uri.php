<?php
// Nastavení chybových hlášení pro ladění
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Zahájení session pro práci s sezením
session_start();


// Načtení dalších potřebných souborů a funkcí
require 'functions.php';

// Kontrola a obnova tokenu pokud je potřeba
ensureTokenIsValid();

// Zkontrolujte, zda je v URL přítomen parametr 'code'
if(isset($_GET['code'])) {
    $authorization_code = $_GET['code'];
    $client_id = $sumUp['client_id'];
    $client_secret = $sumUp['client_secret'];
    $redirect_uri = $sumUp['redirect_uri'];
    

    // Inicializace cURL pro komunikaci s API
    $ch = curl_init();
    
    // Nastavení cURL parametrů pro autentizační dotaz
    curl_setopt($ch, CURLOPT_URL,"https://api.sumup.com/token");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,
                "grant_type=authorization_code&client_id=$client_id&client_secret=$client_secret&redirect_uri=$redirect_uri&code=$authorization_code");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // Získání stavového kódu HTTP z cURL dotazu
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Kontrola, zda API vrátilo nějakou chybu
    if ($httpCode >= 400) {
        echo "API returned an error: " . $httpCode . "<br>";
        echo "<pre>";
        print_r($response);
        echo "</pre>";
        die();
    }

    // Provedení cURL dotazu
    $server_output = curl_exec($ch);

    // Kontrola chyb v cURL dotazu
    if ($server_output === false) {
        echo 'cURL error: ' . curl_error($ch) . "<br>";
    }

    // Ukončení cURL dotazu
    curl_close ($ch);

    // Dekódování JSON odpovědi z API
    $response = json_decode($server_output, true);
    
    // Kontrola, zda odpověď obsahuje potřebné tokeny
    if (isset($response['access_token']) && isset($response['refresh_token'])) {
        $access_token = $response['access_token'];
        $refresh_token = $response['refresh_token'];

        // Výpočet času vypršení platnosti tokenu
        #$expiration = date("Y-m-d H:i:s", time() + $response['expires_in']); xxx
        $expiration = time() + $response['expires_in'];


        // Uložení tokenů do databáze
        saveTokensToDatabase($access_token, $refresh_token, $expiration);
        
        echo "Mám access_token<br>"; 
        $_SESSION['access_token'] = $response['access_token'];
        echo "<pre>";
        print_r($response);
        echo "</pre>";

        echo "Tokeny uloženy do databáze<br>";
    }
}
