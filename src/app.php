<?php

$config = require_once 'config/app.php';

if (is_file('resource/furnidata.xml') === false) {
    exit('err: furnidata not found');
}

$pdo = new PDO("mysql:host={$config['hostname']};dbname={$config['database']}", $config['username'], $config['password'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

$query = $pdo->prepare('SELECT id FROM items_base WHERE item_name = ? LIMIT 1');

$xml = simplexml_load_file('resource/furnidata.xml');

$missing = null;

foreach ($xml->xpath('//furnitype') as $item) {
    $query->execute([ (string) $item->attributes()->classname ]);

    if ($query->rowCount() === 1) {
        continue;
    }

    echo "missing: {$item->attributes()->classname}\n";
    $missing .= "{$item->asXML()}\n";
}

file_put_contents('resource/missing.xml', $missing);

exit('all done - created resource/missing.xml');
