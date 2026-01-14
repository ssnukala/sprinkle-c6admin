<?php

declare(strict_types=1);

/*
 * UserFrosting C6Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/ssnukala/sprinkle-c6admin
 * @copyright Copyright (c) 2024 Srinivas Nukala
 * @license   https://github.com/ssnukala/sprinkle-c6admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\C6Admin\Controller\Dashboard;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\GroupInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\RoleInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Exceptions\ForbiddenException;

/**
 * Dashboard API action.
 * 
 * Provides dashboard statistics and data for the admin panel:
 * - User, role, and group counts
 * - Latest users (up to 8 most recently created)
 * 
 * This endpoint powers the main admin dashboard view with key metrics
 * and recent activity. Requires 'uri_dashboard' permission.
 */
class DashboardApi
{
    /**
     * Inject dependencies.
     *
     * @param Authenticator  $authenticator The authenticator for access control
     * @param Config         $config        The configuration service
     * @param UserInterface  $userModel     The user model interface
     * @param RoleInterface  $roleModel     The role model interface
     * @param GroupInterface $groupModel    The group model interface
     */
    public function __construct(
        protected Authenticator $authenticator,
        protected Config $config,
        protected UserInterface $userModel,
        protected RoleInterface $roleModel,
        protected GroupInterface $groupModel,
    ) {
    }

    /**
     * Get dashboard data.
     * 
     * Returns dashboard statistics including entity counts and recent users.
     *
     * @param Request  $request  The PSR-7 request
     * @param Response $response The PSR-7 response
     *
     * @return Response JSON response with dashboard data
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
     * Validate user has access to the dashboard.
     *
     * @throws ForbiddenException If user lacks 'uri_dashboard' permission
     */
    protected function validateAccess(): void
    {
        if (!$this->authenticator->checkAccess('uri_dashboard')) {
            throw new ForbiddenException();
        }
    }

    /**
     * Gather dashboard statistics and data.
     * 
     * Collects:
     * - Total counts of users, roles, and groups
     * - Latest users (most recently created)
     *
     * @param Request $request The PSR-7 request (unused but required by interface)
     *
     * @return array{counter: array{users: int, roles: int, groups: int}, users: array} Dashboard data
     */
    protected function handle(Request $request): array
    {
        return [
            'counter'   => [
                'users'  => $this->userModel::count(),
                'roles'  => $this->roleModel::count(),
                'groups' => $this->groupModel::count(),
            ],
            'users'     => $this->getLatestUsers(),
        ];
    }

    /**
     * Get the most recently created users.
     * 
     * Returns up to 8 of the most recently created users, ordered by creation date descending.
     * TODO: Make the number of users configurable via application config.
     *
     * @return array<int, array> Array of user data arrays
     */
    protected function getLatestUsers(): array
    {
        // Probably a better way to do this
        // TODO : User config for number of users to display
        $users = $this->userModel::orderBy('created_at', 'desc')
                 ->take(8)
                 ->get();

        return $users->toArray();
    }
}
