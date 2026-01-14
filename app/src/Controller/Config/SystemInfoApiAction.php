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
use Illuminate\Database\Connection;
use PDO;
use PDOException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Exceptions\ForbiddenException;
use UserFrosting\Sprinkle\SprinkleManager;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/**
 * System information API action.
 * 
 * Provides detailed system information about the UserFrosting installation:
 * - Framework version
 * - PHP version
 * - Database connection details (type, version, name)
 * - Web server software
 * - Project path
 * - Installed sprinkles
 * 
 * The information is cached indefinitely for performance and cleared when cache is cleared.
 * Requires 'uri_dashboard' permission (TODO: Create dedicated permission).
 */
class SystemInfoApiAction
{
    /**
     * Inject dependencies.
     *
     * @param Authenticator             $authenticator     The authenticator for access control
     * @param Cache                     $cache             The cache repository for storing system info
     * @param Config                    $config            The configuration service
     * @param Connection                $dbConnection      The database connection
     * @param SprinkleManager           $sprinkleManager   The sprinkle manager
     * @param ResourceLocatorInterface  $locator           The resource locator for paths
     */
    public function __construct(
        protected Authenticator $authenticator,
        protected Cache $cache,
        protected Config $config,
        protected Connection $dbConnection,
        protected SprinkleManager $sprinkleManager,
        protected ResourceLocatorInterface $locator,
    ) {
    }

    /**
     * Get system information.
     * 
     * Returns comprehensive system information about the UserFrosting installation.
     * The data is cached indefinitely to avoid repeated expensive operations.
     *
     * @param Request  $request  The PSR-7 request
     * @param Response $response The PSR-7 response
     *
     * @return Response JSON response with system information
     * 
     * @throws ForbiddenException If user lacks 'uri_dashboard' permission
     */
    public function __invoke(Request $request, Response $response): Response
    {
        $this->validateAccess();
        $payload = $this->handle($request);

        // Write json response
        $payload = json_encode($payload, JSON_THROW_ON_ERROR);
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Validate user has access to system information.
     * 
     * Currently checks for 'uri_dashboard' permission as a temporary measure.
     * TODO: Create a dedicated 'view_system_info' permission for this endpoint.
     *
     * @throws ForbiddenException If user lacks required permission
     */
    protected function validateAccess(): void
    {
        // TODO : Create a dedicated permission for this
        if (!$this->authenticator->checkAccess('uri_dashboard')) {
            throw new ForbiddenException();
        }
    }

    /**
     * Gather and return system information.
     * 
     * Collects comprehensive system data and caches it indefinitely.
     * The cache key 'system_info' is cleared when the application cache is cleared.
     *
     * @param Request $request The PSR-7 request (unused but required by interface)
     *
     * @return array{frameworkVersion: string, phpVersion: string, database: array, server: string, projectPath: string, sprinkles: string[]} System information
     */
    protected function handle(Request $request): array
    {
        return $this->cache->rememberForever('system_info', function () {
            return [
                'frameworkVersion' => (string) \Composer\InstalledVersions::getPrettyVersion('userfrosting/framework'),
                'phpVersion'       => phpversion(),
                'database'         => $this->getDatabaseInfo(),
                'server'           => $_SERVER['SERVER_SOFTWARE'] ?? '',
                'projectPath'      => $this->locator->getBasePath(),
                'sprinkles'        => $this->sprinkleManager->getSprinklesNames(),
            ];
        });
    }

    /**
     * Get database connection information.
     * 
     * Retrieves database details including connection name, database name,
     * driver type (e.g., mysql, pgsql), and server version.
     * Handles PDO exceptions gracefully by returning safe default values.
     *
     * @return array{connection: string, name: string, type: string, version: string} Database information
     */
    protected function getDatabaseInfo(): array
    {
        $database = $this->config->getString('db.default', '');
        $pdo = $this->dbConnection->getPdo();
        $results = [
            'connection' => $database,
            'name'       => $this->dbConnection->getDatabaseName(),
        ];

        try {
            $results['type'] = strval($pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
        } catch (PDOException $e) {
            $results['type'] = 'Unknown';
        }

        try {
            $results['version'] = strval($pdo->getAttribute(PDO::ATTR_SERVER_VERSION));
        } catch (PDOException $e) {
            $results['version'] = '';
        }

        return $results;
    }
}
