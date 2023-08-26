<?php
// functions.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once 'config.php';


/*
refreshToken();
Tato funkce se zabývá pouze obnovováním tokenu.
Pokud je poskytnut refresh_token, použije ho k obnovení access tokenu. Pokud není poskytnut, vezme nejnovější refresh_token z databáze a pokusí se obnovit access token.
Pokud je obnovení úspěšné, funkce uloží nový access token a refresh token do databáze a vrátí nový access token. Pokud je obnovení neúspěšné, vrátí false.

ensureTokenIsValid();
Tato funkce prověřuje, zda je aktuální access token stále platný.
Pokud token vypršel, funkce automaticky zavolá refreshToken() k obnovení tokenu.
Pokud je obnovení úspěšné, funkce vrátí true, což znamená, že máte platný access token. Pokud není obnovení úspěšné, vrátí false.
Představte si scénář, kdy váš skript potřebuje provádět API volání. Před samotným voláním byste mohl zavolat ensureTokenIsValid(), aby se ujistil, že máte platný access token. 
Pokud token vyprší, ensureTokenIsValid() by za vás automaticky obnovil token.

Na druhou stranu, pokud jen potřebujete manuálně obnovit token (např. pro nějaký administrační účel), můžete přímo zavolat refreshToken();
*/

// Funkce pro uložení nových tokenů do databáze.
function saveTokensToDatabase($access_token, $refresh_token, $expiration) {
    global $database; // Načte globální instanci databáze.

    // Vloží nové tokeny do tabulky sumup_tokens.
    $database->insert('sumup_tokens', [
        'access_token' => $access_token,
        'refresh_token' => $refresh_token,
        'expiration' => $expiration
    ]);
}

// Funkce pro získání posledního refresh tokenu z databáze.
function getLatestRefreshToken() {
    global $database; // Načte globální instanci databáze.

    // Vrací poslední refresh token dle data vytvoření (nejnovější první).
    $token = $database->get('sumup_tokens', 'refresh_token', [
        'ORDER' => ['created_at' => 'DESC']
    ]);

    return $token;
}

// Funkce pro obnovení access tokenu.
function refreshToken($refresh_token = null) {
    global $sumUp;
    global $client_id, $client_secret, $database; // Načte globální proměnné a instanci databáze.

    // Pokud není poskytnut refresh token, získejte jej z databáze.
    if (!$refresh_token) {
        $refresh_token = getLatestRefreshToken();
        if (!$refresh_token) {
            return null;
        }
    }

    // Vytvoří cURL požadavek pro obnovení tokenu.
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,"https://api.sumup.com/token");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,
                #"grant_type=refresh_token&client_id=$client_id&client_secret=$client_secret&refresh_token=$refresh_token");
                "grant_type=refresh_token&client_id={$sumUp['client_id']}&client_secret={$sumUp['client_secret']}&refresh_token=$refresh_token");

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $server_output = curl_exec($ch);
    curl_close ($ch);

    $response = json_decode($server_output, true);

   // Pokud je v odpovědi nový access token.
if(isset($response['access_token'])) {
    $new_access_token = $response['access_token'];
    $new_expires_in = $response['expires_in'];
    $new_expiration = time() + $new_expires_in;
    $new_refresh_token = $response['refresh_token'];

    // Vložte nové tokeny do databáze.
    $database->insert("sumup_tokens", [
        "access_token" => $new_access_token,
        "expiration" => $new_expiration,
        "refresh_token" => $new_refresh_token
    ]);
    error_log("Nový token byl úspěšně vložen do databáze");
    return $new_access_token;
} else {
    // Loguje chybu pokud obnovení tokenu selže.
    error_log("Chyba při obnovení tokenu: " . json_encode($response));
    return false;
}
}

// Funkce pro ověření platnosti access tokenu.
function ensureTokenIsValid() {
    global $database; // Načte globální instanci databáze.

    // Získání aktuálních informací o tokenu.
    $tokenInfo = $database->get("sumup_tokens", "*", ["ORDER" => ["created_at" => "DESC"]]);

      // Pokud token existuje a je stále platný, vrátí true.
      if ($tokenInfo && isset($tokenInfo['expiration']) && $tokenInfo['expiration'] > time()) {
        return true;
    } else {
        // Pokud token vyprší, pokusíme se ho obnovit.
        $newAccessToken = refreshToken();
        return $newAccessToken !== false;
    }


}
/*


    */