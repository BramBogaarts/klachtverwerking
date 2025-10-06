<?php
require __DIR__ . '/../vendor/autoload.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Monolog\Level;
use Monolog\Logger; 
use Monolog\Handler\StreamHandler;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable( '../');
$dotenv->load();
var_dump($_ENV['MIJN_EMAIL']);
var_dump($_ENV['PASSWORD']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $naam   = trim($_POST['naam'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $klacht = trim($_POST['klacht'] ?? '');

    if ($naam === '' || $email === '' || $klacht === '') {
        echo "<p style='color:red;'>Vul alle velden in.</p>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<p style='color:red;'>Ongeldig e-mailadres.</p>";
    } else {
        $mail = new PHPMailer(true);

        try {
            // --- Gmail SMTP instellingen met TLS (poort 587) ---
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username = $_ENV['MIJN_EMAIL'];    // <<< vul hier je Gmail-adres in
            $mail->Password = $_ENV['PASSWORD'];    // <<< vul hier je Gmail App-wachtwoord in
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
 
            // Afzender & ontvangers
            $mail->setFrom($_ENV['MIJN_EMAIL'], 'Klantenservice'); 
            $mail->addAddress($email, $naam);               // naar gebruiker
            $mail->addCC($_ENV['MIJN_EMAIL']);         // cc naar jezelf
           

            // Mail inhoud
            $mail->isHTML(true);
            $mail->Subject = 'Uw klacht is in behandeling';
            $mail->Body    = "
                <h2>Uw klacht is ontvangen</h2>
                <p>Beste <b>{$naam}</b>,</p>
                <p>Wij hebben uw klacht ontvangen en nemen deze in behandeling.</p>
                <p><b>Uw gegevens:</b></p>
                <ul>
                    <li><b>Naam:</b> {$naam}</li>
                    <li><b>E-mail:</b> {$email}</li>
                    <li><b>Klacht:</b> " . nl2br(htmlspecialchars($klacht)) . "</li>
                </ul>
                <p>Met vriendelijke groet,<br>Klantenservice</p>
            ";
            $mail->AltBody = "Naam: $naam\nE-mail: $email\nKlacht: $klacht";

            $mail->send();

            $log = new Logger('mailer');
            $logFile = '../Log/info.log';
            $log->pushHandler(new StreamHandler($logFile, Level::Info));
            $log->info('E-mail is verzonden naar:'. $email . $klacht);
            $log->debug('debug');

            $log->warning('dit is een waarschuwingsbericht');
            $log->error('dit is een error');
            echo "<p style='color:green;'>✅ Uw klacht is verstuurd en u ontvangt een bevestiging per e-mail.</p>";
        } catch (Exception $e) {
            echo "<p style='color:red;'>❌ Fout bij verzenden: " . htmlspecialchars($mail->ErrorInfo) . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Klachtenformulier</title>
</head>
<body>
    <h1>Klachtenformulier</h1>
    <form method="post">
        <label for="naam">Naam</label><br>
        <input type="text" id="naam" name="naam" required><br><br>

        <label for="email">E-mailadres</label><br>
        <input type="email" id="email" name="email" required><br><br>

        <label for="klacht">Uw klacht</label><br>
        <textarea id="klacht" name="klacht" rows="5" required></textarea><br><br>

        <button type="submit">Verstuur klacht</button>
    </form>
</body>
</html>

