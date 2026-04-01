<?php
// Nur POST-Anfragen erlauben
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Daten aus dem Formular bereinigen
    $name    = strip_tags(trim($_POST["name"]));
    $email   = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $phone   = strip_tags(trim($_POST["phone"]));
    $date    = strip_tags(trim($_POST["date"]));
    $message = strip_tags(trim($_POST["message"]));

    // DEINE E-MAIL ADRESSE HIER EINTRAGEN
    $recipient = "kontakt@lightsight-music.de";
    $subject   = "Neue Buchungsanfrage von $name";

    // E-Mail Inhalt erstellen
    $email_content = "Name: $name\n";
    $email_content .= "Email: $email\n";
    $email_content .= "Telefon: $phone\n";
    $email_content .= "Datum des Events: $date\n\n";
    $email_content .= "Nachricht:\n$message\n";

    // E-Mail Header
    $email_headers = "From: $name <$email>";

    // E-Mail versenden
    if (mail($recipient, $subject, $email_content, $email_headers)) {
        // Erfolg an JavaScript zurückgeben
        http_response_code(200);
        echo "Vielen Dank! Deine Nachricht wurde gesendet.";
    } else {
        // Fehler beim Versenden
        http_response_code(500);
        echo "Ups! Etwas ist schiefgelaufen.";
    }

} else {
    // Kein POST-Request
    http_response_code(403);
    echo "Es gab ein Problem bei der Übermittlung.";
}
?>