<?php
// config.mailtrap.php
// Configuration centralisée pour les tests d'envoi d'email avec Mailtrap

//return [
//    'host' => 'sandbox.smtp.mailtrap.io',
//    'port' => 2525,
//    'username' => 'e2d2fcf5abd22f', // Remplace par ton identifiant Mailtrap
//    'password' => '6dd0b549cd6d46', // Remplace par ton mot de passe Mailtrap
//    'from_email' => 'mailtrap@demomailtrap.com',
//    'from_name' => 'Test Mailtrap',
//];

return [
    'host' => 'messagerie.sncf.fr',
    'port' => 25,

    'SMTPAuth' => true,
    'SMTPAutoTLS' => false,
    'SMTPSecure' => false,

    'username' => 'fleet-mgr_svc',
    'password' => '847HFT?PvC!wiOIb,2TWcf7r478iopMl.@60',
    'from_email' => 'cen_cvl_stf_sct@sncf.fr',
    'from_name' => 'STF SCT Fleet manager',
];