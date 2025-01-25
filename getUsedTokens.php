<?php
header('Content-Type: application/json');

$usedTokensFile = "used_tokens.json";
$usedTokens = [];

if (file_exists($usedTokensFile)) {
    $usedTokens = json_decode(file_get_contents($usedTokensFile), true);
}

echo json_encode($usedTokens);
?>