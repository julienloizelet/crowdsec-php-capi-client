<?php

require_once __DIR__ . '/../../../../vendor/autoload.php';

use CrowdSec\CapiClient\RequestHandler\FileGetContents;
use CrowdSec\CapiClient\Storage\FileStorage;
use CrowdSec\CapiClient\Watcher;

echo \PHP_EOL . 'Instantiate watcher ...' . \PHP_EOL;
$configs = ['machine_id_prefix' => 'CapiClientTest', 'user_agent_suffix' => 'CapiClientTest'];
$scenarios = [];
echo \PHP_EOL . 'Instantiate custom request handler ...' . \PHP_EOL;
$customRequestHandler = new FileGetContents();
$client = new Watcher($configs, new FileStorage(), $customRequestHandler);
echo 'Watcher instantiated' . \PHP_EOL;

echo 'Calling ' . $client->getConfig('api_url') . ' for decisions stream ...' . \PHP_EOL;
$response = $client->getStreamDecisions($scenarios);
echo 'Decisions stream response is:' . json_encode($response) . \PHP_EOL;
