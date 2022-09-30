<?php

declare(strict_types=1);

namespace CrowdSec\CapiClient\RequestHandler;

use CrowdSec\CapiClient\HttpMessage\Request;
use CrowdSec\CapiClient\HttpMessage\Response;

/**
 * Abstract request handler.
 *
 * @author    CrowdSec team
 *
 * @see      https://crowdsec.net CrowdSec Official Website
 *
 * @copyright Copyright (c) 2020+ CrowdSec
 * @license   MIT License
 */
interface RequestHandlerInterface
{
    /**
     * Performs an HTTP request and returns a response.
     */
    public function handle(Request $request): Response;
}
