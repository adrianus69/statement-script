<?php
if (isset($_POST['artist_name'])) {
    $artist_name = trim($_POST['artist_name']);
    if ($artist_name !== '') {
        $filename = 'clients/clients.txt';
        file_put_contents($filename, $artist_name . PHP_EOL, FILE_APPEND | LOCK_EX);
        echo 'Artist added';
    } else {
        echo 'Invalid artist name';
    }
} else {
    echo 'No artist name provided';
}
?>