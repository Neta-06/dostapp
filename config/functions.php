<?php
function loadLanguage($lang = 'tr') {
    $file = "lang/{$lang}.json";
    if (file_exists($file)) {
        return json_decode(file_get_contents($file), true);
    }
    return json_decode(file_get_contents("lang/tr.json"), true);
}

function generateVerificationCode() {
    return sprintf("%06d", mt_rand(1, 999999));
}

function sendWhatsAppMessage($phone, $message) {
    // WhatsApp API entegrasyonu - ÖRNEK KOD
    // Gerçek uygulamada Twilio, MessageBird vb. servis kullanılmalı
    
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Twilio örneği (kurulum gerektirir)
    /*
    require_once 'vendor/autoload.php';
    
    $sid = "your_twilio_sid";
    $token = "your_twilio_token";
    $twilio = new Twilio\Rest\Client($sid, $token);
    
    try {
        $message = $twilio->messages
            ->create("whatsapp:+90" . $phone,
                array(
                    "from" => "whatsapp:+14155238886",
                    "body" => $message
                )
            );
        return true;
    } catch (Exception $e) {
        error_log("WhatsApp gönderim hatası: " . $e->getMessage());
        return false;
    }
    */
    
    // Şimdilik simülasyon yapıyoruz
    error_log("WHATSAPP SIMULATION: To: $phone, Message: $message");
    return true; // Simülasyon başarılı
}

// Telefon numarası formatlama
function formatPhoneNumber($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phone) === 10) {
        return preg_replace('/(\d{3})(\d{3})(\d{2})(\d{2})/', '$1 $2 $3 $4', $phone);
    }
    return $phone;
}
?>
