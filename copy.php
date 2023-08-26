<?php
// Inicializace session
session_start();

// Nastavení chování chyb PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Import konfiguračního souboru a funkcí
require_once "config.php";
require_once "functions.php";

// Získání přístupového tokenu z databáze
$accessToken = $database->get("sumup_tokens", "access_token");

// Ověření, zda je token stále platný
ensureTokenIsValid();

// Načtení podrobností o tokenu
$tokenData = $database->get("sumup_tokens", ["access_token", "expiration", "refresh_token"], [
    "ORDER" => ["created_at" => "DESC"]
]);
$accessToken = $tokenData['access_token'];

// Nastavení aktuálního data a data o 3 dny zpátky
$currentDate = new DateTime();
$end_date = $currentDate->format('Y-m-d');
$currentDate2 = clone $currentDate;
$currentDate2->modify('-3 day');
$start_date = $currentDate2->format('Y-m-d');

// Inicializace cURL session pro získání transakcí
$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://api.sumup.com/v0.1/me/financials/transactions?start_date=' . $start_date . '&end_date=' . $end_date . '&payment_types[]=POS&payment_types[]=ECOM',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => array(
        'Accept: application/json',
        'Authorization: Bearer ' . $accessToken
    ),
));

// Spuštění cURL requestu a uložení odpovědi
$response = curl_exec($curl);

// Kontrola chyb v cURL requestu
if (curl_errno($curl)) {
    die('Curl error: ' . curl_error($curl));
}
curl_close($curl);

// Dekódování odpovědi do pole
$transactions = json_decode($response, true);
$transactionIDs = [];

// Kontrola, zda jsou nějaké transakce k dispozici
if (is_array($transactions) && !empty($transactions)) {
    foreach ($transactions as $transaction) {
        $transactionIDs[] = $transaction['id'];
    }

    // Iterace přes všechna ID transakcí pro získání detailních informací
    foreach ($transactionIDs as $transaction_id) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.sumup.com/v0.1/me/transactions?id=' . $transaction_id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $accessToken,
                'Accept: application/json'
            ),
        ));

        // Získání odpovědi z SumUp API
        $response = curl_exec($curl);

        // Kontrola chyb cURL
        if (curl_errno($curl)) {
            echo 'Chyba: ' . curl_error($curl);
        } else {
            // Dekódování odpovědi do pole
            $decodedResponse = json_decode($response, true);

            // Kontrola, zda je transakce úspěšná a zda je platba typu POS nebo ECOM
            if (isset($decodedResponse['status']) && $decodedResponse['status'] === 'SUCCESSFUL') {
                if (isset($decodedResponse['payment_type']) && in_array($decodedResponse['payment_type'], ['POS', 'ECOM'])) {

                    // Přeformátování data z transakce
                    $originalDate = $decodedResponse['timestamp'];
                    $date = new DateTime($originalDate);
                    $formattedDate = $date->format('Y-m-d H:i:s');

                    // Získání posledního čísla účtenky z databáze
                    $lastRow = $database->select('receipts', 'receipt_number', [
                        "ORDER" => ["receipt_number" => "DESC"],
                        "LIMIT" => 1
                    ]);

                    // Kontrola, zda existuje poslední číslo účtenky, a přiřazení hodnoty
                    if ($lastRow) {
                        $number = $lastRow[0];
                    } else {
                        $number = null;
                    }

                    // Kontrola, zda už existuje záznam o transakci v databázi
                    if (!$database->has('receipts', ['transaction_id' => $decodedResponse['id']])) {
                        $number++; // Inkrementace čísla účtenky

                        // Vložení nového záznamu o transakci do databáze
                        $database->insert('receipts', [
                            "provider_name" => "Petr Lízal",
                            "address" => "Žatčany, 273, 664 53",
                            "ic" => "14305631",
                            "vat_status" => "Neplátce DPH (zákonem)",
                            "email" => $decodedResponse['username']  ?? null,
                            "web" => "dobrodruzi.cz",
                            "date" => $formattedDate,
                            "receipt_number" => $number,
                            "receipt_prefix" => "BP-",
                            "cashier" => "Playseat-1",
                            "service_description" => "Dovednostní kurz pc her",
                            "service_duration" => "10 minut",
                            "total_amount" => $decodedResponse['amount'] ?? null,
                            "currency" => $decodedResponse['currency'] ?? null,
                            "payment_type" => (isset($decodedResponse["payment_type"])) ?? null,
                            "merchant_code" => $decodedResponse['merchant_code'] ?? null,
                            "transaction_code" => $decodedResponse['transaction_code'] ?? null,
                            "card_type" => isset($decodedResponse['card']['type']) ?? null,
                            "card_last_4_digits" => (isset($decodedResponse['card']['last_4_digits'])) ?? null,
                            "transaction_id" => $decodedResponse['id'],
                            "product_summary" => $decodedResponse['product_summary'] ?? null,
                            "external_reference"  => $decodedResponse['external_reference'] ?? null,
                            "status" => "Transakce byla úspěšná."
                      
                        ]);

                        // Inkrementace čísla účtenky pro další iteraci
                        $number += 1;
                    }
                }
            } else {
                echo 'Chyba: nebylo nic zapsáno do databáze..<br>';
            }
        }
        curl_close($curl);
    }
} else {
    echo "No transactions found";
}

// Oznámení o úspěšném zápisu do databáze
echo "Zapsáno";
?>








                         