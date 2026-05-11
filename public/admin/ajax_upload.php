<?php
session_start();
require_once '../../private/php/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $type = $_POST['type'] ?? 'berita'; // 'berita' or 'proker'
    $upload_dir = '../images/' . ($type == 'proker' ? 'proker/' : 'berita/');
    
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file = $_FILES['file'];
    $file_name = time() . '_' . preg_replace('/[^a-zA-Z0-9.-]/', '_', $file['name']);
    $target_file = $upload_dir . $file_name;

    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        // Return path relative to 'public' folder
        $path = 'images/' . ($type == 'proker' ? 'proker/' : 'berita/') . $file_name;
        echo json_encode(['success' => true, 'path' => $path]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Gagal mengunggah file.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request.']);
}

