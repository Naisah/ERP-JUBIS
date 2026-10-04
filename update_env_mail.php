<?php
$envFile = '.env';
$contents = file_get_contents($envFile);

$replacements = [
    '/^MAIL_MAILER=.*$/m' => 'MAIL_MAILER=smtp',
    '/^MAIL_HOST=.*$/m' => 'MAIL_HOST=smtp.gmail.com',
    '/^MAIL_PORT=.*$/m' => 'MAIL_PORT=465',
    '/^MAIL_USERNAME=.*$/m' => 'MAIL_USERNAME="YOUR_REAL_GMAIL_HERE@gmail.com"',
    '/^MAIL_PASSWORD=.*$/m' => 'MAIL_PASSWORD="gxqmbcsrozrszzey"',
    '/^MAIL_ENCRYPTION=.*$/m' => 'MAIL_ENCRYPTION=tls',
    '/^MAIL_FROM_ADDRESS=.*$/m' => 'MAIL_FROM_ADDRESS="YOUR_REAL_GMAIL_HERE@gmail.com"',
    '/^MAIL_FROM_NAME=.*$/m' => 'MAIL_FROM_NAME="Jubis Marketing ERP"'
];

// If MAIL_ENCRYPTION doesn't exist, append it.
if (strpos($contents, 'MAIL_ENCRYPTION') === false) {
    $contents = str_replace('MAIL_PASSWORD=null', "MAIL_PASSWORD=null\nMAIL_ENCRYPTION=tls", $contents);
}

foreach ($replacements as $pattern => $replacement) {
    $contents = preg_replace($pattern, $replacement, $contents);
}

file_put_contents($envFile, $contents);
echo "Done";
