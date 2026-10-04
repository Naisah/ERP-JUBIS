<?php
$f = "app/Http/Controllers/Admin/ProductController.php";
$c = file_get_contents($f);

// Store validation
$c = str_replace("'in_stock' => 'boolean',", "'stock_quantity' => 'required|integer|min:0',\n            'unit_of_measure' => 'required|string|max:50',", $c);

// Update block store
$c = str_replace("'in_stock' => \$request->boolean('in_stock', true),", "'stock_quantity' => \$validated['stock_quantity'],\n            'unit_of_measure' => \$validated['unit_of_measure'],", $c);

// Update block update
$c = str_replace("'in_stock' => \$request->boolean('in_stock', false),", "'stock_quantity' => \$validated['stock_quantity'],\n            'unit_of_measure' => \$validated['unit_of_measure'],", $c);

file_put_contents($f, $c);
echo "Done";
