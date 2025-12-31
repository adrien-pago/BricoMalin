<?php

$passphrase = 'change_this_passphrase_in_production';
$privateKeyPath = __DIR__ . '/config/jwt/private.pem';
$publicKeyPath = __DIR__ . '/config/jwt/public.pem';

// Générer la clé privée
$config = [
    'digest_alg' => 'sha512',
    'private_key_bits' => 4096,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
];

$resource = openssl_pkey_new($config);
if (!$resource) {
    die("Erreur lors de la génération de la clé privée\n");
}

openssl_pkey_export($resource, $privateKey, $passphrase);
file_put_contents($privateKeyPath, $privateKey);

// Extraire la clé publique
$publicKey = openssl_pkey_get_details($resource);
file_put_contents($publicKeyPath, $publicKey['key']);

echo "Clés JWT générées avec succès !\n";
echo "Private key: $privateKeyPath\n";
echo "Public key: $publicKeyPath\n";

