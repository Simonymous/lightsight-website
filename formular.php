<?php
// Nur POST-Anfragen erlauben
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Konfiguration laden
    // config.php wird nicht in Github getracked, um das Secret nicht zu leaken!
    $config = include('config.php');

    // --- 1. reCAPTCHA VERIFIZIERUNG ---
    $recaptcha_secret = $config['recaptcha_secret'];
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';

    // Prüfen, ob das Captcha überhaupt angeklickt wurde
    if (empty($recaptcha_response)) {
        http_response_code(400);
        echo "Bitte bestätigen Sie, dass Sie kein Roboter sind.";
        exit;
    }

    // Anfrage an den Google Server senden, um Token zu prüfen
    $verify_url = "https://www.google.com/recaptcha/api/siteverify?secret={$recaptcha_secret}&response={$recaptcha_response}";
    $verify_response = file_get_contents($verify_url);
    $response_data = json_decode($verify_response);

    // Wenn Google sagt, der Test ist fehlgeschlagen
    if (!$response_data->success) {
        http_response_code(400);
        echo "reCAPTCHA Verifizierung fehlgeschlagen.";
        exit;
    }
    // -----------------------------------


    // --- 2. DATEN BEREINIGEN ---
    $name    = strip_tags(trim($_POST["name"] ?? ''));
    // Zeilenumbrüche aus dem Namen entfernen (verhindert Header Injection)
    $name    = str_replace(array("\r","\n"), array(" "," "), $name);

    $email   = filter_var(trim($_POST["email"] ?? ''), FILTER_SANITIZE_EMAIL);
    $phone   = strip_tags(trim($_POST["phone"] ?? ''));
    $date    = strip_tags(trim($_POST["date"] ?? ''));
    $message = strip_tags(trim($_POST["message"] ?? ''));

    // Validierung: Sind die Felder ausgefüllt?
    if (empty($name) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($date)) {
        http_response_code(400);
        echo "Bitte füllen Sie alle Pflichtfelder korrekt aus.";
        exit;
    }


    // --- 3. E-MAIL VERSAND ---
    $recipient = "kontakt@lightsight-music.de";
    $subject   = "Neue Buchungsanfrage von $name";

    $email_content  = "Name: $name\n";
    $email_content .= "Email: $email\n";
    $email_content .= "Telefon: $phone\n";
    $email_content .= "Datum des Events: $date\n\n";
    $email_content .= "Nachricht:\n$message\n";

    // E-Mail Header sicher aufbauen
    // Absender ist DEINE Domain (wichtig gegen Spamfilter), "Reply-To" ist der Kunde
    $email_headers  = "From: Band Website <noreply@lightsight-music.de>\r\n";
    $email_headers .= "Reply-To: $email\r\n";
    $email_headers .= "MIME-Version: 1.0\r\n";
    $email_headers .= "Content-Type: text/plain; charset=utf-8\r\n";

    if (mail($recipient, $subject, $email_content, $email_headers)) {
        http_response_code(200);
        echo "Vielen Dank! Deine Nachricht wurde gesendet.";
    } else {
        http_response_code(500);
        echo "Ups! Etwas ist schiefgelaufen.";
    }

} else {
    // Kein POST-Request
    http_response_code(403);
    echo "Es gab ein Problem bei der Übermittlung.";
}
?>