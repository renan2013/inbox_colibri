<?php
// read_file_content.php
// This script reads and outputs content of a file from the uploads directory.
// IMPORTANT: Implement robust security checks to prevent directory traversal and unauthorized access.

if (isset($_GET['file'])) {
    $file_name = basename($_GET['file']); // Get only the file name to prevent directory traversal
    $file_path = __DIR__ . '/uploads/' . $file_name; // Construct the full path

    // Basic security check: Ensure the file exists and is within the uploads directory
    if (file_exists($file_path) && is_file($file_path)) {
        // Determine content type for header
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file_path);
        finfo_close($finfo);

        header('Content-Type: ' . $mime_type);
        readfile($file_path);
    } else {
        http_response_code(404);
        echo "File not found. Debug Info: __DIR__ = " . __DIR__ . ", file_path = " . $file_path;
    }
} else {
    http_response_code(400);
    echo "Bad request.";
}
?>