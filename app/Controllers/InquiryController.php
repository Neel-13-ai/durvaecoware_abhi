<?php
/**
 * Inquiry Controller
 * Durva Eco Ware
 */

require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/config/auth.php';
require_once BASE_PATH . '/app/Models/Inquiry.php';
require_once BASE_PATH . '/app/Models/Setting.php';
require_once BASE_PATH . '/app/Helpers/sanitizer.php';
require_once BASE_PATH . '/app/Helpers/csrf.php';

class InquiryController {
    public static function handleSubmit(): void {
        require_csrf();

        $name = clean_string($_POST['name'] ?? '');
        $phone = clean_phone($_POST['phone'] ?? '');
        $email = clean_email($_POST['email'] ?? '');
        $company = clean_string($_POST['company'] ?? '');
        $productId = clean_int($_POST['product_id'] ?? 0);
        $productInterest = clean_string($_POST['product_interest'] ?? '');
        $quantity = clean_string($_POST['quantity'] ?? '');
        $message = clean_string($_POST['message'] ?? '');

        if (empty($name) || empty($phone)) {
            if (is_ajax()) {
                json_response(['success' => false, 'message' => 'Name and Phone number are required.'], 422);
            }
            flash('error', 'Name and Phone number are required.', 'error');
            redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
        }

        $userId = is_logged_in() ? current_user()['id'] : null;

        $inquiryId = Inquiry::create([
            'user_id'          => $userId,
            'product_id'       => $productId ?: null,
            'name'             => $name,
            'phone'            => $phone,
            'email'            => $email,
            'company'          => $company,
            'product_interest' => $productInterest,
            'quantity'         => $quantity,
            'message'          => $message
        ]);

        // Generate WhatsApp direct text
        $waNumber = Setting::get('whatsapp_number', '919876543210');
        $waText = "Wholesale Inquiry - Durva Eco Ware\n\n" .
                  "Inquiry ID: #$inquiryId\n" .
                  "Name: $name\n" .
                  "Phone: $phone\n" .
                  (!empty($email) ? "Email: $email\n" : "") .
                  (!empty($company) ? "Company: $company\n" : "") .
                  (!empty($productInterest) ? "Product: $productInterest\n" : "") .
                  (!empty($quantity) ? "Quantity: $quantity\n" : "") .
                  (!empty($message) ? "Details: $message" : "");

        $waUrl = "https://wa.me/" . preg_replace('/[^0-9]/', '', $waNumber) . "?text=" . rawurlencode($waText);

        if (is_ajax()) {
            json_response([
                'success'     => true,
                'inquiry_id'  => $inquiryId,
                'message'     => 'Thank you! Your wholesale quote request has been submitted.',
                'whatsapp_url'=> $waUrl
            ]);
        }

        flash('success', 'Thank you! Your wholesale inquiry (#DW-' . $inquiryId . ') has been received. Our team will contact you shortly.');
        
        // If user wants to open WhatsApp right away
        if (!empty($_POST['open_whatsapp'])) {
            header("Location: $waUrl");
            exit;
        }

        redirect($_SERVER['HTTP_REFERER'] ?? 'index.php#wholesale');
    }
}
