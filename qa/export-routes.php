<?php
require __DIR__.'/bootstrap.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$rows = [];
foreach (\Illuminate\Support\Facades\Route::getRoutes() as $route) {
    if (str_starts_with($route->uri(), 'storage/')) continue;
    $action = $route->getActionName(); $implemented = true;
    if (str_contains($action, '@')) {
        [$controller, $method] = explode('@', $action);
        $implemented = method_exists($controller, $method);
    }
    $rows[] = ['methods' => array_values(array_diff($route->methods(), ['HEAD'])), 'uri' => '/'.ltrim($route->uri(), '/'), 'name' => $route->getName(),
        'action' => $action, 'middleware' => $route->gatherMiddleware(), 'handler_exists' => $implemented];
}
if (!is_dir(__DIR__.'/data')) mkdir(__DIR__.'/data', 0777, true);
file_put_contents(__DIR__.'/data/routes.json', json_encode($rows, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
echo count($rows)." routes exported; no application database accessed.\n";
