<?php

declare(strict_types=1);

/*
 * UserFrosting C6Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/ssnukala/sprinkle-c6admin
 * @copyright Copyright (c) 2024 Srinivas Nukala
 * @license   https://github.com/ssnukala/sprinkle-c6admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\C6Admin\Mail;

use Slim\Views\Twig;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Core\Mail\EmailRecipient;
use UserFrosting\Sprinkle\Core\Mail\Mailer;
use UserFrosting\Sprinkle\Core\Mail\TwigMailMessage;

/**
 * User created email notification.
 * 
 * Sends an email notification to a newly created user with their account details.
 * The email is rendered using a Twig template and sent via the configured mailer.
 * 
 * This is typically used when an administrator creates a new user account to notify
 * the user that their account has been created and provide them with initial
 * login instructions.
 */
class UserCreatedEmail
{
    /**
     * Inject dependencies.
     *
     * @param Config $config The configuration service for email settings
     * @param Twig   $twig   The Twig rendering engine
     * @param Mailer $mailer The mailer service for sending emails
     */
    public function __construct(
        protected Config $config,
        protected Twig $twig,
        protected Mailer $mailer,
    ) {
    }

    /**
     * Send user created notification email.
     * 
     * Sends an email to the newly created user notifying them that their account
     * has been created. The email is sent from the admin address configured in
     * the application settings.
     *
     * @param UserInterface $user     The newly created user to send the email to
     * @param string        $template The Twig template path (defaults to standard template)
     * 
     * @throws \Exception If email sending fails
     */
    public function send(UserInterface $user, string $template = 'mail/user-created.html.twig'): void
    {
        // Create and send verification email
        $message = new TwigMailMessage($this->twig, $template);

        // @phpstan-ignore-next-line Config limitation
        $message->from($this->config->get('address_book.admin'))
                ->addEmailRecipient(new EmailRecipient($user->email, $user->full_name))
                ->addParams([
                    'user' => $user,
                ]);

        $this->mailer->send($message);
    }
}
