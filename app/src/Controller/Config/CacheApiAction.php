<?php

declare(strict_types=1);

/*
 * UserFrosting C6Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/ssnukala/sprinkle-c6admin
 * @copyright Copyright (c) 2024 Srinivas Nukala
 * @license   https://github.com/ssnukala/sprinkle-c6admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\C6Admin\Controller\Config;

use Illuminate\Cache\Repository as Cache;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Exceptions\ForbiddenException;
use UserFrosting\Sprinkle\Core\Bakery\ClearCacheCommand;
use UserFrosting\Sprinkle\Core\Util\ApiResponse;

/**
 * Cache management API action.
 * 
 * Handles cache clearing operations for the application, including:
 * - Illuminate cache (application cache)
 * - Twig template cache
 * - Router cache
 * 
 * Requires 'clear_cache' permission.
 * 
 * @see ClearCacheCommand For the underlying cache clearing implementation
 */
class CacheApiAction
{
    /**
     * Inject dependencies.
     *
     * @param Translator         $translator         The translator for internationalized messages
     * @param Authenticator      $authenticator      The authenticator for access control
     * @param ClearCacheCommand  $clearCacheCommand  The cache clearing command
     */
    public function __construct(
        protected Translator $translator,
        protected Authenticator $authenticator,
        protected ClearCacheCommand $clearCacheCommand,
    ) {
    }

    /**
     * Clear all application caches.
     * 
     * This method handles the HTTP request to clear all caches:
     * - Illuminate cache (application data cache)
     * - Twig template cache
     * - Router cache
     *
     * @param Request  $request  The PSR-7 request
     * @param Response $response The PSR-7 response
     *
     * @return Response JSON response with success message
     * 
     * @throws ForbiddenException If user lacks 'clear_cache' permission
     */
    public function __invoke(Request $request, Response $response): Response
    {
        $this->validateAccess();
        $this->clearCacheCommand->clearIlluminateCache();
        $this->clearCacheCommand->clearTwigCache();
        $this->clearCacheCommand->clearRouterCache();

        // Message
        $message = $this->translator->translate('SITE_CONFIG.CACHE.CLEARED');

        // Write response
        $payload = new ApiResponse($message);
        $response->getBody()->write((string) $payload);

        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Validate access to the page.
     *
     * @throws ForbiddenException
     */
    protected function validateAccess(): void
    {
        if (!$this->authenticator->checkAccess('clear_cache')) {
            throw new ForbiddenException();
        }
    }
}
