<?php
/**
 * Beehive Lodge Contact Form Handler
 * This PHP script handles contact form submissions and sends emails via Gmail API or SMTP
 */

// Set proper headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Configuration
$config = [
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_username' => 'VOTRE-EMAIL@gmail.com', // Remplacez par votre Gmail
    'smtp_password' => 'VOTRE-MOT-DE-PASSE-APP', // Mot de passe d'application Gmail
    'from_email' => 'VOTRE-EMAIL@gmail.com',
    'from_name' => 'Beehive Lodge',
    'to_email' => 'VOTRE-EMAIL@gmail.com',      // Email de réception
    'notification_email' => 'VOTRE-EMAIL@gmail.com'
];

try {
    // Get and validate input data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid input data');
    }
    
    // Validate required fields
    $required_fields = ['firstName', 'lastName', 'email', 'subject', 'message'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            throw new Exception("Le champ {$field} est obligatoire");
        }
    }
    
    // Sanitize input data
    $data = [
        'firstName' => sanitize($input['firstName']),
        'lastName' => sanitize($input['lastName']),
        'email' => filter_var($input['email'], FILTER_VALIDATE_EMAIL),
        'phone' => sanitize($input['phone'] ?? ''),
        'subject' => sanitize($input['subject']),
        'message' => sanitize($input['message']),
        'newsletter' => (bool)($input['newsletter'] ?? false),
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // Validate email
    if (!$data['email']) {
        throw new Exception('Adresse email invalide');
    }
    
    // Check for spam (basic protection)
    if (isSpam($data['message']) || isSpam($data['firstName'])) {
        throw new Exception('Message rejeté par le filtre anti-spam');
    }
    
    // Create email content
    $emailContent = createEmailContent($data);
    
    // Send confirmation email to customer
    $customerEmail = createCustomerEmailContent($data);
    
    // Send emails
    $adminEmailSent = sendEmail(
        $config['to_email'],
        'Nouveau message de contact - Beehive Lodge',
        $emailContent,
        $config
    );
    
    $customerEmailSent = sendEmail(
        $data['email'],
        'Confirmation de votre message - Beehive Lodge',
        $customerEmail,
        $config
    );
    
    // Log the contact form submission
    logSubmission($data);
    
    // Add to newsletter if requested
    if ($data['newsletter']) {
        addToNewsletter($data['email'], $data['firstName'] . ' ' . $data['lastName']);
    }
    
    // Return success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Message envoyé avec succès',
        'timestamp' => $data['timestamp']
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Sanitize input data
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Basic spam detection
 */
function isSpam($text) {
    $spamPatterns = [
        '/\b(viagra|casino|poker|loan|money|win|prize|lottery)\b/i',
        '/http[s]?:\/\/(?![yourdomain\.com])/i', // External links
        '/\b\d{10,}\b/', // Long numbers (phone spam)
    ];
    
    foreach ($spamPatterns as $pattern) {
        if (preg_match($pattern, $text)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Create email content for admin
 */
function createEmailContent($data) {
    return "
    <html>
    <head>
        <style>
            body { font-family: 'Poppins', Arial, sans-serif; line-height: 1.6; color: #2C3E50; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #D4A017, #B8860B); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f8f9fa; padding: 20px; border-radius: 0 0 10px 10px; }
            .field { margin-bottom: 15px; }
            .label { font-weight: bold; color: #D4A017; }
            .value { margin-top: 5px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Nouveau message de contact - Beehive Lodge</h2>
            </div>
            <div class='content'>
                <div class='field'>
                    <div class='label'>Nom complet:</div>
                    <div class='value'>{$data['firstName']} {$data['lastName']}</div>
                </div>
                <div class='field'>
                    <div class='label'>Email:</div>
                    <div class='value'>{$data['email']}</div>
                </div>
                <div class='field'>
                    <div class='label'>Téléphone:</div>
                    <div class='value'>" . ($data['phone'] ?: 'Non renseigné') . "</div>
                </div>
                <div class='field'>
                    <div class='label'>Sujet:</div>
                    <div class='value'>{$data['subject']}</div>
                </div>
                <div class='field'>
                    <div class='label'>Message:</div>
                    <div class='value'>" . nl2br($data['message']) . "</div>
                </div>
                <div class='field'>
                    <div class='label'>Newsletter:</div>
                    <div class='value'>" . ($data['newsletter'] ? 'Oui' : 'Non') . "</div>
                </div>
                <div class='field'>
                    <div class='label'>Date:</div>
                    <div class='value'>{$data['timestamp']}</div>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
}

/**
 * Create confirmation email for customer
 */
function createCustomerEmailContent($data) {
    return "
    <html>
    <head>
        <style>
            body { font-family: 'Poppins', Arial, sans-serif; line-height: 1.6; color: #2C3E50; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #D4A017, #B8860B); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f8f9fa; padding: 20px; border-radius: 0 0 10px 10px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Merci pour votre message</h2>
            </div>
            <div class='content'>
                <p>Cher/Chère {$data['firstName']},</p>
                <p>Nous avons bien reçu votre message concernant : <strong>{$data['subject']}</strong></p>
                <p>Notre équipe vous répondra dans les plus brefs délais, généralement sous 24 heures.</p>
                <p>En attendant, n'hésitez pas à nous contacter directement au <strong>+33 1 23 45 67 89</strong> si vous avez une demande urgente.</p>
                <p>Cordialement,<br>L'équipe Beehive Lodge</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

/**
 * Send email using SMTP
 */
function sendEmail($to, $subject, $body, $config) {
    // Use PHPMailer or similar library in production
    $headers = [
        'From: ' . $config['from_name'] . ' <' . $config['from_email'] . '>',
        'Reply-To: ' . $config['from_email'],
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8'
    ];
    
    return mail($to, $subject, $body, implode("\r\n", $headers));
}

/**
 * Log form submission
 */
function logSubmission($data) {
    $logFile = '../logs/contact_submissions.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logEntry = json_encode($data) . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

/**
 * Add email to newsletter (implement your newsletter service integration)
 */
function addToNewsletter($email, $name) {
    // Integrate with your newsletter service (Mailchimp, SendinBlue, etc.)
    // For now, just log it
    $logFile = '../logs/newsletter_signups.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logEntry = json_encode([
        'email' => $email,
        'name' => $name,
        'timestamp' => date('Y-m-d H:i:s')
    ]) . "\n";
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}
?>
