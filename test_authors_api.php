<?php

require_once __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

// Test the authors API directly
$request = \Illuminate\Http\Request::create('/api/available-filters?type=authors', 'GET');
$request->headers->set('Accept', 'application/json');

$response = $app->handle($request);

echo "Status: " . $response->getStatusCode() . "\n";
echo "Content: " . $response->getContent() . "\n";