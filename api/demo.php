<?php
/**
 * Beehive Lodge Demo Request Handler
 * This PHP script handles demo booking requests
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
    'smtp_username' => 'VOTRE-EMAIL@gmail.com',    // Remplacez par votre Gmail
    'smtp_password' => 'VOTRE-MOT-DE-PASSE-APP',   // Mot de passe d'application Gmail
    'from_email' => 'VOTRE-EMAIL@gmail.com',
    'from_name' => 'Beehive Lodge',
    'to_email' => 'VOTRE-EMAIL@gmail.com',         // Email de réception
];

try {
    // Get and validate input data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid input data');
    }
    
    // Validate required fields
    $required_fields = ['firstName', 'lastName', 'email', 'phone', 'preferredDate', 'timeSlot', 'guests'];
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
        'phone' => sanitize($input['phone']),
        'company' => sanitize($input['company'] ?? ''),
        'preferredDate' => sanitize($input['preferredDate']),
        'timeSlot' => sanitize($input['timeSlot']),
        'guests' => intval($input['guests']),
        'interests' => is_array($input['interests']) ? $input['interests'] : [],
        'message' => sanitize($input['message'] ?? ''),
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // Validate email
    if (!$data['email']) {
        throw new Exception('Adresse email invalide');
    }
    
    // Validate date (must be in the future)
    $demoDate = strtotime($data['preferredDate']);
    if ($demoDate <= time()) {
        throw new Exception('La date de démonstration doit être dans le futur');
    }
    
    // Check availability (basic check - implement your booking system logic)
    if (!isDateAvailable($data['preferredDate'], $data['timeSlot'])) {
        throw new Exception('Ce créneau n\'est pas disponible. Veuillez choisir une autre date/heure.');
    }
    
    // Create booking record
    $bookingId = createBooking($data);
    
    // Send confirmation emails
    $adminEmail = createAdminDemoEmail($data, $bookingId);
    $customerEmail = createCustomerDemoEmail($data, $bookingId);
    
    sendEmail(
        $config['to_email'],
        'Nouvelle demande de démonstration - Beehive Lodge',
        $adminEmail,
        $config
    );
    
    sendEmail(
        $data['email'],
        'Confirmation de votre demande de démonstration - Beehive Lodge',
        $customerEmail,
        $config
    );
    
    // Return success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Demande de démonstration envoyée avec succès',
        'bookingId' => $bookingId,
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
 * Check if date and time slot is available
 */
function isDateAvailable($date, $timeSlot) {
    // Implement your booking availability logic here
    // For now, return true (assuming availability)
    
    // Example logic:
    // - Check against existing bookings in database
    // - Exclude Sundays or specific holidays
    // - Limit number of demos per day
    
    $dayOfWeek = date('N', strtotime($date));
    if ($dayOfWeek == 7) { // Sunday
        return false;
    }
    
    return true;
}

/**
 * Create booking record
 */
function createBooking($data) {
    $bookingId = 'DEMO-' . date('Ymd') . '-' . substr(md5(uniqid()), 0, 6);
    
    // Log booking to file (in production, save to database)
    $bookingsFile = '../logs/demo_bookings.log';
    $logDir = dirname($bookingsFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $bookingData = array_merge($data, ['bookingId' => $bookingId, 'status' => 'pending']);
    $logEntry = json_encode($bookingData) . "\n";
    file_put_contents($bookingsFile, $logEntry, FILE_APPEND | LOCK_EX);
    
    return $bookingId;
}

/**
 * Create admin notification email
 */
function createAdminDemoEmail($data, $bookingId) {
    $interestsText = !empty($data['interests']) ? implode(', ', $data['interests']) : 'Aucun spécifié';
    
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
            .booking-id { background: #D4A017; color: white; padding: 10px; text-align: center; border-radius: 5px; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Nouvelle Demande de Démonstration</h2>
            </div>
            <div class='booking-id'>
                Référence: {$bookingId}
            </div>
            <div class='content'>
                <div class='field'>
                    <div class='label'>Client:</div>
                    <div class='value'>{$data['firstName']} {$data['lastName']}</div>
                </div>
                <div class='field'>
                    <div class='label'>Email:</div>
                    <div class='value'>{$data['email']}</div>
                </div>
                <div class='field'>
                    <div class='label'>Téléphone:</div>
                    <div class='value'>{$data['phone']}</div>
                </div>
                <div class='field'>
                    <div class='label'>Entreprise:</div>
                    <div class='value'>" . ($data['company'] ?: 'Non renseigné') . "</div>
                </div>
                <div class='field'>
                    <div class='label'>Date souhaitée:</div>
                    <div class='value'>" . date('d/m/Y', strtotime($data['preferredDate'])) . "</div>
                </div>
                <div class='field'>
                    <div class='label'>Créneau horaire:</div>
                    <div class='value'>{$data['timeSlot']}</div>
                </div>
                <div class='field'>
                    <div class='label'>Nombre d'invités:</div>
                    <div class='value'>{$data['guests']}</div>
                </div>
                <div class='field'>
                    <div class='label'>Centres d'intérêt:</div>
                    <div class='value'>{$interestsText}</div>
                </div>
                <div class='field'>
                    <div class='label'>Message:</div>
                    <div class='value'>" . ($data['message'] ? nl2br($data['message']) : 'Aucun message') . "</div>
                </div>
                <div class='field'>
                    <div class='label'>Date de demande:</div>
                    <div class='value'>{$data['timestamp']}</div>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
}

/**
 * Create customer confirmation email
 */
function createCustomerDemoEmail($data, $bookingId) {
    $timeSlotText = [
        'morning' => '10h00 - 12h00',
        'afternoon' => '14h00 - 16h00', 
        'evening' => '18h00 - 20h00'
    ][$data['timeSlot']] ?? $data['timeSlot'];
    
    return "
    <html>
    <head>
        <style>
            body { font-family: 'Poppins', Arial, sans-serif; line-height: 1.6; color: #2C3E50; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #D4A017, #B8860B); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f8f9fa; padding: 20px; border-radius: 0 0 10px 10px; }
            .booking-id { background: #D4A017; color: white; padding: 10px; text-align: center; border-radius: 5px; font-weight: bold; margin-bottom: 20px; }
            .info-box { background: white; padding: 15px; border-left: 4px solid #D4A017; margin: 15px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Confirmation de votre démonstration</h2>
            </div>
            <div class='content'>
                <div class='booking-id'>
                    Référence: {$bookingId}
                </div>
                
                <p>Cher/Chère {$data['firstName']},</p>
                
                <p>Nous avons bien reçu votre demande de démonstration et sommes ravis de vous accueillir chez Beehive Lodge.</p>
                
                <div class='info-box'>
                    <h4>Détails de votre réservation:</h4>
                    <p><strong>Date:</strong> " . date('d/m/Y', strtotime($data['preferredDate'])) . "</p>
                    <p><strong>Heure:</strong> {$timeSlotText}</p>
                    <p><strong>Nombre de personnes:</strong> {$data['guests']}</p>
                    <p><strong>Durée:</strong> 2 heures</p>
                </div>
                
                <div class='info-box'>
                    <h4>Ce qui vous attend:</h4>
                    <ul>
                        <li>Visite guidée personnalisée de nos installations</li>
                        <li>Dégustation de notre menu signature</li>
                        <li>Consultation gratuite avec notre équipe</li>
                        <li>Présentation de nos services premium</li>
                        <li>Cadeau de bienvenue exclusif</li>
                    </ul>
                </div>
                
                <p><strong>Important:</strong> Notre équipe vous contactera sous 24h pour confirmer la disponibilité et finaliser les détails de votre visite.</p>
                
                <p>Pour toute question, n'hésitez pas à nous contacter au <strong>+33 1 23 45 67 89</strong>.</p>
                
                <p>À bientôt chez Beehive Lodge !<br>L'équipe Beehive Lodge</p>
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
    $headers = [
        'From: ' . $config['from_name'] . ' <' . $config['from_email'] . '>',
        'Reply-To: ' . $config['from_email'],
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8'
    ];
    
    return mail($to, $subject, $body, implode("\r\n", $headers));
}
?>
