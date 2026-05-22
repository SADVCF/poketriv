<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $request = Illuminate\Http\Request::create('/ranking', 'GET', ['difficulty' => 'league']);
    $response = $kernel->handle($request);
    $status = $response->getStatusCode();
    echo "Status: $status\n";
    if ($status !== 200) {
        echo "Body: " . substr($response->getContent(), 0, 500) . "\n";
    } else {
        echo "OK - page loads successfully\n";
    }
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
