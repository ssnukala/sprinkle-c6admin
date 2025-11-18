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

use Illuminate\Database\Connection;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\Config\Config;
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Authenticate\Hasher;
use UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Log\UserActivityLogger;
use UserFrosting\Sprinkle\Core\Log\DebugLoggerInterface;
use UserFrosting\Sprinkle\Core\Util\ApiResponse;
use UserFrosting\Sprinkle\CRUD6\Controller\UpdateFieldAction as BaseUpdateFieldAction;
use UserFrosting\Sprinkle\CRUD6\Database\Models\Interfaces\CRUD6ModelInterface;
use UserFrosting\Sprinkle\CRUD6\ServicesProvider\SchemaService;

/**
 * Processes the request to update a single field of an existing user record.
 *
 * This extends CRUD6's UpdateFieldAction to handle boolean field toggles properly.
 * For boolean fields with the "toggle" attribute, this action will automatically 
 * toggle the current value if no explicit value is provided in the request.
 *
 * Request type: PUT
 * 
 * @see \UserFrosting\Sprinkle\CRUD6\Controller\UpdateFieldAction
 * @see \UserFrosting\Sprinkle\Admin\Controller\User\UserUpdateFieldAction
 */
class UserUpdateFieldAction extends BaseUpdateFieldAction
{
    /**
     * Inject dependencies.
     */
    public function __construct(
        protected AuthorizationManager $authorizer,
        protected Authenticator $authenticator,
        protected DebugLoggerInterface $logger,
        protected SchemaService $schemaService,
        protected Config $config,
        protected Translator $translator,
        protected UserActivityLogger $userActivityLogger,
        protected Connection $db,
        protected Hasher $hasher,
    ) {
        parent::__construct(
            $authorizer,
            $authenticator,
            $logger,
            $schemaService,
            $config,
            $translator,
            $userActivityLogger,
            $db,
            $hasher
        );
    }

    /**
     * Invoke the controller.
     *
     * This override handles boolean toggle fields by automatically determining
     * the new value based on the current value when no explicit value is provided.
     *
     * @param array               $crudSchema The schema configuration array (auto-injected)
     * @param CRUD6ModelInterface $crudModel  The configured model instance with record loaded (auto-injected)
     * @param Request             $request
     * @param Response            $response
     */
    public function __invoke(array $crudSchema, CRUD6ModelInterface $crudModel, Request $request, Response $response): Response
    {
        // Get the field name from the route
        $fieldName = $this->getParameter($request, 'field');
        $primaryKey = $crudSchema['primary_key'] ?? 'id';
        $recordId = $crudModel->getAttribute($primaryKey);

        $this->debugLog("C6Admin [UserUpdateFieldAction] Processing field update request", [
            'model' => $crudSchema['model'],
            'record_id' => $recordId,
            'field' => $fieldName,
        ]);

        // Check if this field exists in the schema
        if (!isset($crudSchema['fields'][$fieldName])) {
            $this->logger->error("C6Admin [UserUpdateFieldAction] Field does not exist", [
                'model' => $crudSchema['model'],
                'field' => $fieldName,
            ]);
            // Let parent handle the error
            return parent::__invoke($crudSchema, $crudModel, $request, $response);
        }

        $fieldConfig = $crudSchema['fields'][$fieldName];
        
        // Check if this is a boolean toggle field
        $isToggle = ($fieldConfig['type'] ?? '') === 'boolean' 
                    && isset($fieldConfig['toggle']) 
                    && $fieldConfig['toggle'] === true;

        if ($isToggle) {
            // Get current value
            $currentValue = $crudModel->{$fieldName};
            
            // Get request body
            $params = (array) $request->getParsedBody();
            
            // If no value provided, or if toggle is requested, invert the current value
            if (!isset($params[$fieldName]) || (isset($params['toggle']) && $params['toggle'] === true)) {
                $newValue = !$currentValue;
                
                $this->debugLog("C6Admin [UserUpdateFieldAction] Toggle boolean field", [
                    'field' => $fieldName,
                    'current_value' => $currentValue,
                    'new_value' => $newValue,
                ]);
                
                // Create a new request with the calculated value
                $params[$fieldName] = $newValue;
                $request = $request->withParsedBody($params);
            } else {
                // Ensure boolean type
                if (isset($params[$fieldName])) {
                    // Convert string 'true'/'false' to boolean
                    if ($params[$fieldName] === 'true' || $params[$fieldName] === '1' || $params[$fieldName] === 1) {
                        $params[$fieldName] = true;
                    } elseif ($params[$fieldName] === 'false' || $params[$fieldName] === '0' || $params[$fieldName] === 0) {
                        $params[$fieldName] = false;
                    }
                    
                    $this->debugLog("C6Admin [UserUpdateFieldAction] Boolean field value normalized", [
                        'field' => $fieldName,
                        'value' => $params[$fieldName],
                    ]);
                    
                    $request = $request->withParsedBody($params);
                }
            }
        }

        // Call parent implementation with potentially modified request
        return parent::__invoke($crudSchema, $crudModel, $request, $response);
    }
}
