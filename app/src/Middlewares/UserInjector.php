<?php

declare(strict_types=1);

/*
 * UserFrosting C6Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/ssnukala/sprinkle-c6admin
 * @copyright Copyright (c) 2024 Srinivas Nukala
 * @license   https://github.com/ssnukala/sprinkle-c6admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\C6Admin\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteContext;
use UserFrosting\Sprinkle\CRUD6\Middlewares\CRUD6Injector;

/**
 * User Injector Middleware.
 * 
 * Extends CRUD6Injector to work with sprinkle-admin style routes.
 * Injects the 'users' model name into the route before passing to CRUD6Injector.
 */
class UserInjector extends CRUD6Injector
{
    /**
     * @var string Placeholder name for user_name in route
     */
    protected string $placeholder = 'user_name';
    
    /**
     * Process the request, injecting the model name.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        
        // Inject the model name into route args so CRUD6Injector can find it
        if ($route) {
            $route->setArgument('model', 'users');
        }
        
        // Set the model name for getInstance method
        $this->currentModelName = 'users';
        $this->currentConnectionName = null;
        
        // Get user_name from route if present
        $userName = $route?->getArgument('user_name');
        
        // Get the configured model instance
        $instance = $this->getInstance($userName);
        
        // Get schema
        $schema = $this->schemaService->getSchema('users', null);
        
        // Inject both model and schema
        $request = $request
            ->withAttribute('crudModel', $instance)
            ->withAttribute('crudSchema', $schema);
        
        return $handler->handle($request);
    }
    
    /**
     * Store the current model name for use in getInstance.
     * 
     * @var string|null
     */
    private ?string $currentModelName = null;
    
    /**
     * Store the current database connection name for use in getInstance.
     * 
     * @var string|null
     */
    private ?string $currentConnectionName = null;
}
