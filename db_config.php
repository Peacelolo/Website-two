<?php
// Centralized DB config. Update these values to match your local XAMPP MySQL settings.
// On a default XAMPP installation the MySQL root user has an empty password.
// If you set a password for root or created a dedicated user, change accordingly.
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = ''; // <-- most XAMPP installs use an empty password for root
$DB_NAME = 'herdtrace_db';

/**
 * Create and return a mysqli connection. On failure this will return null.
 */
function get_db_connection() {
    global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME;
    try {
        // Suppress warning output; we'll handle errors explicitly
        mysqli_report(MYSQLI_REPORT_OFF);
        $mysqli = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
        if ($mysqli->connect_errno) {
            header('Content-Type: application/json');
            echo json_encode(["error" => "Database connection failed: " . $mysqli->connect_error]);
            return null;
        }
        return $mysqli;
    } catch (Throwable $e) {
        header('Content-Type: application/json');
        echo json_encode(["error" => "Database connection exception: " . $e->getMessage()]);
        return null;
    }
}

?>
