<?php
/**
 * Savory Haven Restaurant - Contact Form Processing
 * This file processes contact form submissions
 */

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize inputs
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_SPECIAL_CHARS);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_SPECIAL_CHARS);
    
    // Validate required fields
    if (empty($name) || empty($email) || empty($message)) {
        $response = [
            'success' => false,
            'message' => 'Please fill in all required fields.'
        ];
        echo json_encode($response);
        exit;
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response = [
            'success' => false,
            'message' => 'Please enter a valid email address.'
        ];
        echo json_encode($response);
        exit;
    }
    
    // In a real application, you would:
    // 1. Send an email to the restaurant
    // 2. Save to a database
    // 3. Send a confirmation email to the customer
    // 4. For demo, we'll save to a log file
    
    // Create a log entry
    $log_entry = date('Y-m-d H:i:s') . " - Contact: $name, $email, Subject: " . ($subject ?: "No subject") . ", Message: $message\n";
    
    // Create a contact directory if it doesn't exist
    if (!file_exists('contact')) {
        mkdir('contact', 0755, true);
    }
    
    // Append to contact log
    file_put_contents('contact/contact.log', $log_entry, FILE_APPEND);
    
    // Return success response
    $response = [
        'success' => true,
        'message' => 'Thank you for your message! Our team will get back to you soon.'
    ];
    
    echo json_encode($response);
    exit;
}
?>