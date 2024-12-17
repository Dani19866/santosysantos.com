<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Cargar PHPMailer mediante Composer

/**
 * Valida y limpia el nombre (solo letras y espacios).
 * @param string $name
 * @return string|false
 */
function validateName($name)
{
    return preg_match('/^[a-zA-Z\s]+$/', $name) ? trim($name) : false;
}

/**
 * Valida y limpia el teléfono (solo números, guiones, paréntesis y espacios).
 * @param string $phone
 * @return string|false
 */
function validatePhone($phone)
{
    // Elimina espacios en los extremos
    $phone = trim($phone);

    // Verifica si el número contiene solo dígitos, paréntesis, guiones, espacios y el símbolo '+'
    if (preg_match('/^[0-9\-\+\(\)\s]+$/', $phone)) {
        // Opcional: Normaliza el formato eliminando espacios extra
        return preg_replace('/\s+/', '', $phone);
    }

    return false;
}

/**
 * Valida el correo electrónico.
 * @param string $email
 * @return string|false
 */
function validateEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Sanitiza la descripción (escapar caracteres especiales).
 * @param string $description
 * @return string
 */
function sanitizeDescription($description)
{
    return htmlspecialchars(trim($description), ENT_QUOTES, 'UTF-8');
}

/**
 * Valida y sanitiza todos los datos del formulario.
 * @param array $data
 * @return array|false
 */
function validateFormData($data)
{
    $name = validateName($data['name'] ?? '');
    $email = validateEmail($data['email'] ?? '');
    $phone = validatePhone($data['phone'] ?? '');
    $description = sanitizeDescription($data['description'] ?? '');

    if ($name && $email && $phone && $description) {
        return compact('name', 'email', 'phone', 'description');
    }

    return false; // Si algún campo no es válido
}

/**
 * Configura y envía un correo usando PHPMailer.
 * @param array $formData Datos validados del formulario.
 * @return bool Indica si el correo fue enviado correctamente.
 */
function sendEmail($formData)
{
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'santosysantosca.noreply@gmail.com'; // Tu correo Gmail
        $mail->Password   = 'C0rr3oAutomatic01'; // Contraseña de aplicación
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet = 'UTF-8';

        // Configuración del correo
        $mail->setFrom('santosysantosca.noreply@gmail.com', 'Daniel');
        $mail->addAddress('deoliveiradaniel200@gmail.com', 'Destinatario');

        // Contenido del correo
        $mail->isHTML(false);
        $mail->Subject = 'Nueva solicitud de formulario';
        $mail->Body    = "Tienes un nuevo mensaje:\n\n" .
            "Nombre: {$formData['name']}\n" .
            "Correo Electrónico: {$formData['email']}\n" .
            "Teléfono: {$formData['phone']}\n" .
            "Descripción:\n{$formData['description']}";

        // Enviar correo
        return $mail->send();
        
    } catch (Exception $e) {
        error_log("Error al enviar correo: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Maneja la lógica principal del formulario.
 */
function handleFormRequest()
{
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $formData = validateFormData($_POST);

        if (!$formData) {
            die("Error: Todos los campos son obligatorios y deben ser válidos.");
        }

        if (sendEmail($formData)) {
            echo "El mensaje se envió correctamente. ¡Gracias!";
        } else {
            echo "Error al enviar el mensaje. Inténtalo de nuevo más tarde.";
        }
    } else {
        echo "Acceso inválido.";
    }
}

// Ejecutar el controlador principal
handleFormRequest();