<?php

namespace CrowdSec\CapiClient\Tests\Integration;

/**
 * Integration Test for watcher.
 *
 * @author    CrowdSec team
 *
 * @see      https://crowdsec.net CrowdSec Official Website
 *
 * @copyright Copyright (c) 2022+ CrowdSec
 * @license   MIT License
 */

use CrowdSec\CapiClient\AbstractClient;
use CrowdSec\CapiClient\ClientException;
use CrowdSec\CapiClient\RequestHandler\FileGetContents;
use CrowdSec\CapiClient\Storage\FileStorage;
use CrowdSec\CapiClient\Tests\Constants as TestConstants;
use CrowdSec\CapiClient\Tests\PHPUnitUtil;
use CrowdSec\CapiClient\Watcher;
use PHPUnit\Framework\TestCase;
use PHPUnit\Util\Exception;

/**
 * @coversNothing
 */
final class WatcherTest extends TestCase
{
    protected $configs = [
        'machine_id_prefix' => TestConstants::MACHINE_ID_PREFIX,
        'user_agent_suffix' => TestConstants::USER_AGENT_SUFFIX,
        'scenarios' => ['crowdsecurity/http-backdoors-attempts'],
    ];

    public function requestHandlerProvider(): array
    {
        return [
            'Default (Curl)' => [null],
            'FileGetContents' => [new FileGetContents()],
        ];
    }

    /**
     * @dataProvider requestHandlerProvider
     */
    public function testDecisionsStream($requestHandler)
    {
        $client = new Watcher($this->configs, new FileStorage(), $requestHandler);
        $this->checkRequestHandler($client, $requestHandler);
        $response = $client->getStreamDecisions();

        $this->assertArrayHasKey('new', $response, 'Response should have a "new" key');
        $this->assertArrayHasKey('deleted', $response, 'Response should have a "deleted" key');
    }

    /**
     * @dataProvider requestHandlerProvider
     */
    public function testPushSignals($requestHandler)
    {
        $client = new Watcher($this->configs, new FileStorage(), $requestHandler);
        $this->checkRequestHandler($client, $requestHandler);
        $signals = $this->getSignals();
        $response = $client->pushSignals($signals);

        PHPUnitUtil::assertRegExp(
            $this,
            '/OK/',
            $response['message'],
            'Signals should be pushed'
        );

        unset($signals[0]["source"]);
        $error = '';
        $code = 0;
        try {
            $client->pushSignals([$signals[0]]);
        } catch (ClientException $e) {
            $error = $e->getMessage();
            $code = $e->getCode();
        }

        $this->assertEquals(400, $code);
        PHPUnitUtil::assertRegExp(
            $this,
            '/missing required properties/',
            $error,
            'Should throw an error for bad formatted signal'
        );
    }

    /**
     * @dataProvider requestHandlerProvider
     */
    public function testEnroll($requestHandler)
    {
        $enrollmentKey = file_get_contents(__DIR__ . '/.enrollment_key.txt');
        if (!$enrollmentKey) {
            throw new Exception('Error while trying to get content of .enrollment_key.txt file');
        }
        $client = new Watcher($this->configs, new FileStorage(), $requestHandler);
        $this->checkRequestHandler($client, $requestHandler);
        $response = $client->enroll('CAPI CLIENT INTEGRATION TEST', false, $enrollmentKey, ['test-tag']);

        PHPUnitUtil::assertRegExp(
            $this,
            '/OK/',
            $response['message'],
            'Instance should be enrolled'
        );

        $error = '';
        $code = 0;
        try {
            $client->enroll('CAPI CLIENT INTEGRATION TEST', false, 'ThisEnrollmentKeyDoesNotExist', ['test-tag']);
        } catch (ClientException $e) {
            $error = $e->getMessage();
            $code = $e->getCode();
        }

        $this->assertEquals(403, $code);
        PHPUnitUtil::assertRegExp(
            $this,
            '/attachment key provided is not valid/',
            $error,
            'Should throw an error for bad enrollment key'
        );
    }

    /**
     * @return void
     */
    private function checkRequestHandler(AbstractClient $client, $requestHandler)
    {
        if (null === $requestHandler) {
            $this->assertEquals(
                'CrowdSec\CapiClient\RequestHandler\Curl',
                get_class($client->getRequestHandler()),
                'Request handler should be curl by default'
            );
        } else {
            $this->assertEquals(
                'CrowdSec\CapiClient\RequestHandler\FileGetContents',
                get_class($client->getRequestHandler()),
                'Request handler should be file_get_contents'
            );
        }
    }

    /**
     * @return array[]
     */
    private function getSignals(): array
    {
        return [
            0 => [
                'message' => 'Ip 1.1.1.1 performed "crowdsecurity / http - path - traversal - probing" (6 events over 29.992437958s) at 2020-11-06 20:14:11.189255784 +0000 UTC m=+52.785061338',
                'scenario' => 'crowdsecurity/http-path-traversal-probing',
                'scenario_hash' => '',
                'scenario_version' => '',
                'source' => [
                    'id' => 1,
                    'as_name' => 'CAPI CLIENT PHP INTEGRATION TEST',
                    'cn' => 'FR',
                    'ip' => '1.1.1.1',
                    'latitude' => 48.9917,
                    'longitude' => 1.9097,
                    'range' => '1.1.1.1/32',
                    'scope' => 'test',
                    'value' => '1.1.1.1',
                ],
                'start_at' => '2020-11-06T20:13:41.196817737Z',
                'stop_at' => '2020-11-06T20:14:11.189252228Z',
            ],
            1 => [
                'message' => 'Ip 2.2.2.2 performed "crowdsecurity / http - probing" (6 events over 29.992437958s) at 2020-11-06 20:14:11.189255784 +0000 UTC m=+52.785061338',
                'scenario' => 'crowdsecurity/http-probing',
                'scenario_hash' => '',
                'scenario_version' => '',
                'source' => [
                    'id' => 2,
                    'as_name' => 'CAPI CLIENT PHP INTEGRATION TEST',
                    'cn' => 'FR',
                    'ip' => '2.2.2.2',
                    'latitude' => 48.9917,
                    'longitude' => 1.9097,
                    'range' => '2.2.2.2/32',
                    'scope' => 'test',
                    'value' => '2.2.2.2',
                ],
                'start_at' => '2020-11-06T20:13:41.196817737Z',
                'stop_at' => '2020-11-06T20:14:11.189252228Z',
            ],
        ];
    }
}
