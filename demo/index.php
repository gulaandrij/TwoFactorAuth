<?php
require_once '../vendor/autoload.php';

use TFAuth\TwoFactorAuth;

$tfa = new TwoFactorAuth('MyApp', 6, 30, 'sha1', new \TFAuth\Providers\Qr\EndroidQRProvider());

echo '<li>First create a secret and associate it with a user';
$secret = $tfa->createSecret(160);  // Though the default is an 80 bits secret (for backwards compatibility reasons) we recommend creating 160+ bits secrets (see RFC 4226 - Algorithm Requirements)


$imageUrl = $tfa->getQRCodeImageAsDataUri('My label', $secret, 200);

echo "<li>Next create a QR code and let the user scan it:<br>
<img src='".$imageUrl."'>
<br>...or display the secret to the user for manual entry: ".chunk_split($secret, 4, ' ');
$code = $tfa->getCode($secret);
echo '<li>Next, have the user verify the code; at this time the code displayed by a 2FA-app would be: <span style="color:#00c">'.$code.'</span> (but that changes periodically)';
echo '<li>When the code checks out, 2FA can be / is enabled; store (encrypted?) secret with user and have the user verify a code each time a new session is started.';
echo '<li>When aforementioned code ('.$code.') was entered, the result would be: '.(($tfa->verifyCode($secret, $code, 0) === true) ? '<span style="color:#0c0">OK</span>' : '<span style="color:#c00">FAIL</span>');


?>
    </ol>
    <p>Note: Make sure your server-time is <a href="http://en.wikipedia.org/wiki/Network_Time_Protocol">NTP-synced</a>!
        Depending on the $discrepancy allowed your time cannot drift too much from the users' time!</p>



