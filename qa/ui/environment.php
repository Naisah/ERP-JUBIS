<?php
require __DIR__.'/../bootstrap.php';
$qaUiDatabase = realpath(__DIR__.'/../results').DIRECTORY_SEPARATOR.'ui.sqlite';
foreach (['APP_ENV'=>'qa-browser','APP_URL'=>'http://127.0.0.1:9001','DB_DATABASE'=>$qaUiDatabase,
    'APP_KEY'=>'base64:'.base64_encode(str_repeat('Q',32))] as $key=>$value) {
    putenv("$key=$value"); $_ENV[$key]=$_SERVER[$key]=$value;
}
$app=require __DIR__.'/../../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if(config('database.default')!=='sqlite'||config('database.connections.sqlite.database')!==$qaUiDatabase||!app()->environment('qa-browser')) {
    throw new RuntimeException('UI QA refused a database other than qa/results/ui.sqlite.');
}
config(['session.driver'=>'file','session.files'=>__DIR__.'/../results/ui-sessions','cache.default'=>'array','mail.default'=>'array']);
config(['services.paymongo.simulation'=>true,'services.lalamove.simulation'=>true]);
// The application provider forces HTTPS outside APP_ENV=local. This QA server
// has no TLS listener; use HTTP here without changing production application code.
\Illuminate\Support\Facades\URL::forceScheme('http');
\Illuminate\Support\Facades\URL::forceRootUrl('http://127.0.0.1:9001');
if(!is_dir(config('session.files'))) mkdir(config('session.files'),0777,true);
\Illuminate\Support\Facades\Vite::useHotFile(__DIR__.'/../results/unused-hot');
\Illuminate\Support\Facades\Mail::fake(); \Illuminate\Support\Facades\Notification::fake();
\Illuminate\Support\Facades\Http::preventStrayRequests();
