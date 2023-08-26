<?php
$client_id = "cc_classic_xyeV3KbZowj7bluE45nsWLyMxEDzq";
$redirect_uri = "https://dobrodruzi.cz/sum-up/redirect_uri.php";  // URL vaší aplikace, kam se má vrátit po autorizaci

$authorization_url = "https://api.sumup.com/authorize?response_type=code&client_id=$client_id&redirect_uri=$redirect_uri";

header('Location: ' . $authorization_url);  // Přesměrování uživatele na SumUp pro autorizaci


