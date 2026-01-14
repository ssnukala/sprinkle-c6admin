<?php

declare(strict_types=1);

/*
 * UserFrosting C6Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/ssnukala/sprinkle-c6admin
 * @copyright Copyright (c) 2024 Srinivas Nukala
 * @license   https://github.com/ssnukala/sprinkle-c6admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\C6Admin\Exceptions;

use UserFrosting\Sprinkle\Core\Exceptions\UserFacingException;
use UserFrosting\Support\Message\UserMessage;

/**
 * Missing required parameter exception.
 * 
 * Thrown when a required parameter is missing from a request.
 * The parameter name is set using the setParam() method and included in the error message.
 */
final class MissingRequiredParamException extends UserFacingException
{
    protected string $title = 'VALIDATE.ERROR';
    protected string $param = '';

    /**
     * {@inheritDoc}
     */
    public function getDescription(): string|UserMessage
    {
        return new UserMessage('VALIDATE.REQUIRED', ['label' => $this->param]);
    }

    /**
     * Set the parameter name for the error message.
     * 
     * This parameter will be included in the user-facing error message
     * to indicate which required field was missing.
     *
     * @param string $param The name of the missing parameter
     *
     * @return static Returns self for method chaining
     */
    public function setParam(string $param): static
    {
        $this->param = $param;

        return $this;
    }
}
