<?php
$filename = 'clients/clients.txt';

if (file_exists($filename)) {
    $artists = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    echo implode("\n", $artists);
} else {
    echo '';
}
?>