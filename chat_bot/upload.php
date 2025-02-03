<?php
$uploadDir = __DIR__ . '/';
if(isset($_FILES['file'])) {
    $tmpName = $_FILES['file']['tmp_name'];
    $fileName = basename($_FILES['file']['name']);
    move_uploaded_file($tmpName, $uploadDir . $fileName);
}
?>