<?php
/**
 * Validation Functions for KK Pharma
 * Provides comprehensive server-side validation for user inputs
 */

/**
 * Sanitize input data
 * @param mixed $data Input data to sanitize
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email address
 * @param string $email Email to validate
 * @return array Validation result with 'valid' boolean and 'message' string
 */
function validateEmail($email) {
    $email = sanitizeInput($email);
    
    if (empty($email)) {
        return ['valid' => false, 'message' => 'Email is required'];
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['valid' => false, 'message' => 'Invalid email format'];
    }
    
    if (strlen($email) > 100) {
        return ['valid' => false, 'message' => 'Email is too long (max 100 characters)'];
    }
    
    return ['valid' => true, 'message' => '', 'sanitized' => $email];
}

/**
 * Validate password strength
 * @param string $password Password to validate
 * @param string $confirm_password Optional confirmation password
 * @return array Validation result
 */
function validatePassword($password, $confirm_password = null) {
    if (empty($password)) {
        return ['valid' => false, 'message' => 'Password is required'];
    }
    
    if (strlen($password) < 8) {
        return ['valid' => false, 'message' => 'Password must be at least 8 characters long'];
    }
    
    if (strlen($password) > 128) {
        return ['valid' => false, 'message' => 'Password is too long (max 128 characters)'];
    }
    
    // Check for at least one uppercase letter
    if (!preg_match('/[A-Z]/', $password)) {
        return ['valid' => false, 'message' => 'Password must contain at least one uppercase letter'];
    }
    
    // Check for at least one lowercase letter
    if (!preg_match('/[a-z]/', $password)) {
        return ['valid' => false, 'message' => 'Password must contain at least one lowercase letter'];
    }
    
    // Check for at least one number
    if (!preg_match('/[0-9]/', $password)) {
        return ['valid' => false, 'message' => 'Password must contain at least one number'];
    }
    
    // Check for at least one special character
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        return ['valid' => false, 'message' => 'Password must contain at least one special character'];
    }
    
    // Check password confirmation if provided
    if ($confirm_password !== null && $password !== $confirm_password) {
        return ['valid' => false, 'message' => 'Passwords do not match'];
    }
    
    return ['valid' => true, 'message' => ''];
}

/**
 * Validate name (first name, last name, full name)
 * @param string $name Name to validate
 * @param string $field_name Field name for error message
 * @return array Validation result
 */
function validateName($name, $field_name = 'Name') {
    $name = sanitizeInput($name);
    
    if (empty($name)) {
        return ['valid' => false, 'message' => "$field_name is required"];
    }
    
    if (strlen($name) < 2) {
        return ['valid' => false, 'message' => "$field_name must be at least 2 characters long"];
    }
    
    if (strlen($name) > 50) {
        return ['valid' => false, 'message' => "$field_name is too long (max 50 characters)"];
    }
    
    // Allow only letters, spaces, hyphens, and apostrophes
    if (!preg_match("/^[a-zA-Z\s\-']+$/", $name)) {
        return ['valid' => false, 'message' => "$field_name can only contain letters, spaces, hyphens, and apostrophes"];
    }
    
    return ['valid' => true, 'message' => '', 'sanitized' => $name];
}

/**
 * Validate phone number
 * @param string $phone Phone number to validate
 * @return array Validation result
 */
function validatePhone($phone) {
    $phone = sanitizeInput($phone);
    
    if (empty($phone)) {
        return ['valid' => false, 'message' => 'Phone number is required'];
    }
    
    // Remove all non-numeric characters
    $phone_numeric = preg_replace('/[^0-9]/', '', $phone);
    
    if (strlen($phone_numeric) < 10) {
        return ['valid' => false, 'message' => 'Phone number must be at least 10 digits'];
    }
    
    if (strlen($phone_numeric) > 15) {
        return ['valid' => false, 'message' => 'Phone number is too long (max 15 digits)'];
    }
    
    // Validate phone format (international format)
    if (!preg_match('/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/', $phone)) {
        return ['valid' => false, 'message' => 'Invalid phone number format'];
    }
    
    return ['valid' => true, 'message' => '', 'sanitized' => $phone];
}

/**
 * Validate address
 * @param string $address Address to validate
 * @param string $field_name Field name for error message
 * @return array Validation result
 */
function validateAddress($address, $field_name = 'Address') {
    $address = sanitizeInput($address);
    
    if (empty($address)) {
        return ['valid' => false, 'message' => "$field_name is required"];
    }
    
    if (strlen($address) < 10) {
        return ['valid' => false, 'message' => "$field_name must be at least 10 characters long"];
    }
    
    if (strlen($address) > 255) {
        return ['valid' => false, 'message' => "$field_name is too long (max 255 characters)"];
    }
    
    return ['valid' => true, 'message' => '', 'sanitized' => $address];
}

/**
 * Validate text field (general purpose)
 * @param string $text Text to validate
 * @param string $field_name Field name for error message
 * @param int $min_length Minimum length
 * @param int $max_length Maximum length
 * @return array Validation result
 */
function validateText($text, $field_name = 'Text', $min_length = 1, $max_length = 255) {
    $text = sanitizeInput($text);
    
    if (empty($text) && $min_length > 0) {
        return ['valid' => false, 'message' => "$field_name is required"];
    }
    
    if (strlen($text) < $min_length) {
        return ['valid' => false, 'message' => "$field_name must be at least $min_length characters long"];
    }
    
    if (strlen($text) > $max_length) {
        return ['valid' => false, 'message' => "$field_name is too long (max $max_length characters)"];
    }
    
    return ['valid' => true, 'message' => '', 'sanitized' => $text];
}

/**
 * Validate numeric value
 * @param mixed $value Value to validate
 * @param string $field_name Field name for error message
 * @param float $min Minimum value
 * @param float $max Maximum value
 * @return array Validation result
 */
function validateNumber($value, $field_name = 'Value', $min = null, $max = null) {
    if (!is_numeric($value)) {
        return ['valid' => false, 'message' => "$field_name must be a number"];
    }
    
    $value = floatval($value);
    
    if ($min !== null && $value < $min) {
        return ['valid' => false, 'message' => "$field_name must be at least $min"];
    }
    
    if ($max !== null && $value > $max) {
        return ['valid' => false, 'message' => "$field_name must be at most $max"];
    }
    
    return ['valid' => true, 'message' => '', 'sanitized' => $value];
}

/**
 * Validate date
 * @param string $date Date string to validate
 * @param string $format Expected date format (default: Y-m-d)
 * @return array Validation result
 */
function validateDate($date, $format = 'Y-m-d') {
    $date = sanitizeInput($date);
    
    if (empty($date)) {
        return ['valid' => false, 'message' => 'Date is required'];
    }
    
    $d = DateTime::createFromFormat($format, $date);
    if (!$d || $d->format($format) !== $date) {
        return ['valid' => false, 'message' => 'Invalid date format'];
    }
    
    return ['valid' => true, 'message' => '', 'sanitized' => $date];
}

/**
 * Validate file upload
 * @param array $file $_FILES array element
 * @param array $allowed_types Allowed MIME types
 * @param int $max_size Maximum file size in bytes
 * @return array Validation result
 */
function validateFile($file, $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'], $max_size = 5242880) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        return ['valid' => false, 'message' => $error_messages[$file['error']] ?? 'Unknown upload error'];
    }
    
    if ($file['size'] > $max_size) {
        return ['valid' => false, 'message' => 'File size exceeds maximum allowed size'];
    }
    
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $finfo->file($file['tmp_name']);
    
    if (!in_array($mime_type, $allowed_types)) {
        return ['valid' => false, 'message' => 'File type not allowed'];
    }
    
    return ['valid' => true, 'message' => ''];
}

/**
 * Validate URL
 * @param string $url URL to validate
 * @return array Validation result
 */
function validateURL($url) {
    $url = sanitizeInput($url);
    
    if (empty($url)) {
        return ['valid' => false, 'message' => 'URL is required'];
    }
    
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return ['valid' => false, 'message' => 'Invalid URL format'];
    }
    
    return ['valid' => true, 'message' => '', 'sanitized' => $url];
}

/**
 * Validate user registration data
 * @param array $data User registration data
 * @return array Validation result with errors array
 */
function validateRegistration($data) {
    $errors = [];
    
    // Validate name
    $name_result = validateName($data['name'] ?? '', 'Full Name');
    if (!$name_result['valid']) {
        $errors['name'] = $name_result['message'];
    }
    
    // Validate email
    $email_result = validateEmail($data['email'] ?? '');
    if (!$email_result['valid']) {
        $errors['email'] = $email_result['message'];
    }
    
    // Validate password
    $password_result = validatePassword($data['password'] ?? '', $data['confirm_password'] ?? null);
    if (!$password_result['valid']) {
        $errors['password'] = $password_result['message'];
    }
    
    // Validate phone (optional but recommended)
    if (!empty($data['phone'])) {
        $phone_result = validatePhone($data['phone']);
        if (!$phone_result['valid']) {
            $errors['phone'] = $phone_result['message'];
        }
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Validate login data
 * @param array $data Login data
 * @return array Validation result with errors array
 */
function validateLogin($data) {
    $errors = [];
    
    // Validate email
    $email_result = validateEmail($data['email'] ?? '');
    if (!$email_result['valid']) {
        $errors['email'] = $email_result['message'];
    }
    
    // Validate password
    if (empty($data['password'] ?? '')) {
        $errors['password'] = 'Password is required';
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Check if email already exists in database
 * @param mysqli $conn Database connection
 * @param string $email Email to check
 * @param string $table Table name (default: register)
 * @param int $exclude_id Optional user ID to exclude (for updates)
 * @return array Validation result
 */
function checkEmailExists($conn, $email, $table = 'register', $exclude_id = null) {
    $email = sanitizeInput($email);
    
    $query = "SELECT id FROM $table WHERE email = ?";
    $params = [$email];
    $types = "s";
    
    if ($exclude_id !== null) {
        $query .= " AND id != ?";
        $params[] = $exclude_id;
        $types .= "i";
    }
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return ['valid' => false, 'message' => 'Email already exists'];
    }
    
    return ['valid' => true, 'message' => ''];
}

/**
 * Generate validation error response
 * @param array $errors Array of errors
 * @return string HTML formatted error message
 */
function getValidationErrorsHTML($errors) {
    if (empty($errors)) {
        return '';
    }
    
    $html = '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
    $html .= '<i class="bi bi-exclamation-circle me-2"></i>';
    $html .= '<ul class="mb-0">';
    
    foreach ($errors as $error) {
        $html .= "<li>$error</li>";
    }
    
    $html .= '</ul>';
    $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    $html .= '</div>';
    
    return $html;
}
?>
