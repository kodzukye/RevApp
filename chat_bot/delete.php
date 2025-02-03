<?php
$uploadDir = __DIR__ . '/';
if(isset($_GET['filename'])) {
    $fileName = basename($_GET['filename']);
    $filePath = $uploadDir . $fileName;
    
    if(file_exists($filePath)) {
        unlink($filePath);
    }
}
?>