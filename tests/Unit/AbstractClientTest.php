<?php

declare(strict_types=1);

namespace CrowdSec\CapiClient\Tests\Unit;

/**
 * Test for watcher.
 *
 * @author    CrowdSec team
 *
 * @see      https://crowdsec.net CrowdSec Official Website
 *
 * @copyright Copyright (c) 2022+ CrowdSec
 * @license   MIT License
 */

use CrowdSec\CapiClient\ClientException;
use CrowdSec\CapiClient\Constants;
use CrowdSec\CapiClient\HttpMessage\Response;
use CrowdSec\CapiClient\Storage\FileStorage;
use CrowdSec\CapiClient\Tests\MockedData;
use CrowdSec\CapiClient\Tests\PHPUnitUtil;
use CrowdSec\CapiClient\Watcher;
use DateTime;
use TypeError;

/**
 * @uses \CrowdSec\CapiClient\HttpMessage\Response
 * @uses \CrowdSec\CapiClient\Configuration::getConfigTreeBuilder
 * @uses \CrowdSec\CapiClient\Watcher::formatUserAgent
 * @uses \CrowdSec\CapiClient\Storage\FileStorage::__construct
 *
 * @covers \CrowdSec\CapiClient\AbstractClient::__construct
 * @covers \CrowdSec\CapiClient\AbstractClient::getConfig
 * @covers \CrowdSec\CapiClient\AbstractClient::getUrl
 * @covers \CrowdSec\CapiClient\AbstractClient::getRequestHandler
 * @covers \CrowdSec\CapiClient\AbstractClient::formatResponseBody
 * @covers \CrowdSec\CapiClient\AbstractClient::getFullUrl
 * @covers \CrowdSec\CapiClient\Watcher::__construct
 * @covers \CrowdSec\CapiClient\Watcher::configure
 */
final class AbstractClientTest extends AbstractClient
{
    public function testClientInit()
    {
        $client = new Watcher($this->configs, new FileStorage());

        $url = $client->getUrl();
        $this->assertEquals(
            Constants::URL_DEV,
            $url,
            'Url should be dev by default'
        );
        $this->assertEquals(
            '/',
            substr($url, -1),
            'Url should end with /'
        );

        $requestHandler = $client->getRequestHandler();
        $this->assertEquals(
            'CrowdSec\CapiClient\RequestHandler\Curl',
            get_class($requestHandler),
            'Request handler must be curl by default'
        );

        $client = new Watcher(array_merge($this->configs, ['env' => Constants::ENV_PROD]), new FileStorage());
        $url = $client->getUrl();
        $this->assertEquals(
            Constants::URL_PROD,
            $url,
            'Url should be prod if specified'
        );
        $this->assertEquals(
            '/',
            substr($url, -1),
            'Url should end with /'
        );

        $error = false;
        try {
            new Watcher($this->configs, new FileStorage(), new DateTime());
        } catch (TypeError $e) {
            $error = $e->getMessage();
        }

        PHPUnitUtil::assertRegExp(
            $this,
            '/must .*RequestHandlerInterface/',
            $error,
            'Bad request handler should throw an error'
        );
    }

    public function testPrivateOrProtectedMethods()
    {
        $client = new Watcher($this->configs, new FileStorage());

        $fullUrl = PHPUnitUtil::callMethod(
            $client,
            'getFullUrl',
            ['/test-endpoint']
        );
        $this->assertEquals(
            Constants::URL_DEV . 'test-endpoint',
            $fullUrl,
            'Full Url should be ok'
        );

        $jsonBody = json_encode(['message' => 'ok']);

        $response = new Response($jsonBody, 200);

        $formattedResponse = ['message' => 'ok'];

        $validateResponse = PHPUnitUtil::callMethod(
            $client,
            'formatResponseBody',
            [$response]
        );
        $this->assertEquals(
            $formattedResponse,
            $validateResponse,
            'Array response should be valid'
        );

        $jsonBody = '{bad response]]]';
        $response = new Response($jsonBody, 200);
        $error = false;
        try {
            PHPUnitUtil::callMethod(
                $client,
                'formatResponseBody',
                [$response]
            );
        } catch (ClientException $e) {
            $error = $e->getMessage();
        }

        PHPUnitUtil::assertRegExp(
            $this,
            '/not a valid json/',
            $error,
            'Bad JSON should be detected'
        );

        $response = new Response(MockedData::REGISTER_ALREADY, 200);

        $decodedResponse = PHPUnitUtil::callMethod(
            $client,
            'formatResponseBody',
            [$response]
        );

        $this->assertEquals(
            ['message' => 'User already registered.'],
            $decodedResponse,
            'Decoded response should be correct'
        );

        $response = new Response(MockedData::UNAUTHORIZED, 403);

        $error = false;
        try {
            PHPUnitUtil::callMethod(
                $client,
                'formatResponseBody',
                [$response]
            );
        } catch (ClientException $e) {
            $error = $e->getMessage();
        }

        PHPUnitUtil::assertRegExp(
            $this,
            '/403.*Unauthorized/',
            $error,
            'Should throw error on 403'
        );

        $response = new Response('', 200);

        $error = false;
        $decoded = [];
        try {
            $decoded = PHPUnitUtil::callMethod(
                $client,
                'formatResponseBody',
                [$response]
            );
        } catch (ClientException $e) {
            $error = true;
        }

        $this->assertEquals(
            false,
            $error,
            'An empty response body should not throw error'
        );

        $this->assertEquals(
            ['message' => ''],
            $decoded,
            'An empty response body should not return some array'
        );

        $response = new Response('', 500);

        $error = false;
        try {
            PHPUnitUtil::callMethod(
                $client,
                'formatResponseBody',
                [$response]
            );
        } catch (ClientException $e) {
            $error = $e->getMessage();
        }

        PHPUnitUtil::assertRegExp(
            $this,
            '/500.*/',
            $error,
            'An empty response body should throw error for bad status'
        );

        $error = false;
        try {
            new Response(['test'], 200);
        } catch (TypeError $e) {
            $error = $e->getMessage();
        }

        PHPUnitUtil::assertRegExp(
            $this,
            '/type .*string/',
            $error,
            'If response body is not a string it should throw error'
        );
    }
}
