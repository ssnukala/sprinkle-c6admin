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
 * User password reset action.
 * 
 * Processes an admin request to revoke a user's password, forcing them to reset
 * it upon their next login attempt. This is done by setting the user's
 * password_last_set attribute to null.
 * 
 * This is an administrative action that requires authentication and the
 * 'update_user_field' permission. The user model is injected by CRUD6Injector
 * middleware from the route parameter.
 * 
 * Route: POST /api/c6/users/{id}/password-reset
 * 
 * @see CRUD6Injector For how the user model is injected into the request
 */
class UserPasswordResetAction
{
    /**
     * Inject dependencies.
     *
     * @param Translator    $translator    The translator for internationalized messages
     * @param Authenticator $authenticator The authenticator for access control
     */
    public function __construct(
        protected Translator $translator,
        protected Authenticator $authenticator,
    ) {
    }

    /**
     * Handle password reset request.
     * 
     * Retrieves the user from the request (injected by CRUD6Injector middleware),
     * expires their password, and returns a success message.
     *
     * @param Request  $request  The PSR-7 request with 'crudModel' attribute containing the user
     * @param Response $response The PSR-7 response
     *
     * @return Response JSON response with success message
     * 
     * @throws \RuntimeException If user model is not found in request attributes
     * @throws ForbiddenException If user lacks 'update_user_field' permission
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
     * Process the password reset.
     * 
     * Validates access and expires the user's password.
     *
     * @param UserInterface $user The user whose password should be reset
     * 
     * @throws ForbiddenException If user lacks 'update_user_field' permission
     */
    protected function handle(UserInterface $user): void
    {
        $this->validateAccess($user);
        $this->expireUserPassword($user);
    }

    /**
     * Validate admin has permission to reset user passwords.
     *
     * @param UserInterface $user The user whose password will be reset (unused but available for future checks)
     * 
     * @throws ForbiddenException If user lacks 'update_user_field' permission
     */
    protected function validateAccess(UserInterface $user): void
    {
        if (!$this->authenticator->checkAccess('update_user_field')) {
            throw new ForbiddenException();
        }
    }

    /**
     * Expire the user's password.
     * 
     * Sets the password_last_set attribute to null, which forces the user
     * to reset their password the next time they attempt to log in.
     * The password itself remains in the database but is marked as expired.
     *
     * @param UserInterface $user The user whose password should be expired
     */
    protected function expireUserPassword(UserInterface $user): void
    {
        $user->password_last_set = null;
        $user->save();
    }
}
