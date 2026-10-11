<?php
$uri=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
$public=realpath(__DIR__.'/../../public'); $candidate=realpath($public.DIRECTORY_SEPARATOR.ltrim(rawurldecode($uri),'/'));
if($candidate&&str_starts_with($candidate,$public.DIRECTORY_SEPARATOR)&&is_file($candidate)) return false;
require __DIR__.'/environment.php';
header('X-Jubis-QA-Environment: isolated-ui');
if(!file_exists($qaUiDatabase)) { http_response_code(503); echo 'Run php qa/ui/seed.php first.'; exit; }
$kernel=$app->make(\Illuminate\Contracts\Http\Kernel::class);
$response=$kernel->handle($request=\Illuminate\Http\Request::capture()); $response->send(); $kernel->terminate($request,$response);
