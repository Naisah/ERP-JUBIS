<?php
$content = file_get_contents('routes/web.php');
$replace = "        \->whereIn('brand', \);\n    }\n\n    if (\->filled('sort')) {\n        if (\->sort === 'name') {\n            \->orderBy('name', 'asc');\n        } elseif (\->sort === 'brand') {\n            \->orderBy('brand', 'asc');\n        }\n    }\n";
$content = str_replace("        \->whereIn('brand', \);\n    }", $replace, $content);
$content = str_replace("'filters' => \->only(['search', 'categories', 'brands'])", "'filters' => \->only(['search', 'categories', 'brands', 'sort'])", $content);
file_put_contents('routes/web.php', $content);
