<?php
$prods = json_decode(file_get_contents('database/seeders/master_products.json'), true);
$count = 0;
foreach ($prods as $p) {
    if ($p['id'] == 871) $count++;
}
echo "ID 871 appears $count times\n";
