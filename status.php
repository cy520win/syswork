<?php 
$lines  = file('./DEPLOY');
$items = [];
foreach ($lines as $line) {
    $line = trim($line);
    if (!$line) continue;
    list($key, $value) = explode('|', $line);
    $items[$key] = $value;
} 
echo json_encode($items);
