<?php

declare(strict_types=1);

/*
 * UserFrosting C6Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/ssnukala/sprinkle-c6admin
 * @copyright Copyright (c) 2024 Srinivas Nukala
 * @license   https://github.com/ssnukala/sprinkle-c6admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\C6Admin\Controller\User;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Exceptions\ForbiddenException;
use UserFrosting\Sprinkle\Core\Util\ApiResponse;
use UserFrosting\Sprinkle\CRUD6\Database\Models\Interfaces\CRUD6ModelInterface;

/**
 * Processes an admin request to reset a user password.
 *
 * Handles an admin request to revoke a user's password. This action will require
 * the user to reset their password using the "reset password" feature upon their
 * next login. This route requires authentication.
 *
 * Route: /api/users/{id}/password-reset
 * Request type: POST
 */
class UserPasswordResetAction
{
    /**
     * Inject dependencies.
     */
    public function __construct(
        protected Translator $translator,
        protected Authenticator $authenticator,
    ) {
    }

    /**
     * Receive the request, dispatch to the handler, and return the payload to
     * the response.
     *
     * @param Request  $request
     * @param Response $response
     */
    public function __invoke(Request $request, Response $response): Response
    {
        // Get the user from the request attributes (injected by CRUD6Injector)
        $user = $request->getAttribute('crudModel');
        
        if (!$user instanceof UserInterface) {
            throw new \RuntimeException('User model not found in request');
        }

        $this->handle($user);

        // Message
        $message = $this->translator->translate('USER.ADMIN.PASSWORD_RESET_SUCCESS', $user->toArray());

        // Write response
        $payload = new ApiResponse($message);
        $response->getBody()->write((string) $payload);

        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Handle the request.
     *
     * @param UserInterface $user
     */
    protected function handle(UserInterface $user): void
    {
        $this->validateAccess($user);
        $this->expireUserPassword($user);
    }

    /**
     * Validate access to the page.
     *
     * @throws ForbiddenException
     */
    protected function validateAccess(UserInterface $user): void
    {
        if (!$this->authenticator->checkAccess('update_user_field')) {
            throw new ForbiddenException();
        }
    }

    /**
     * Invalidate the user's password by setting the password_last_set attribute
     * to null. This forces the user to reset their password the next time they
     * log in.
     *
     * @param UserInterface $user The user to reset the password for
     */
    protected function expireUserPassword(UserInterface $user): void
    {
        $user->password_last_set = null;
        $user->save();
    }
}
