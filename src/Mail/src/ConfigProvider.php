<?php

declare(strict_types=1);

namespace Mail;

use Laminas\Mail\Transport\TransportInterface;

/**
 * The configuration provider for the Audit module
 *
 * @see https://docs.mezzio.dev/mezzio/v3/getting-started/features/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     *
     * @return array
     */
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    /**
     * Returns the container dependencies
     *
     * @return array
     */
    public function getDependencies()
    {
        return [
            'invokables' => [],
            'factories'  => [
                TransportInterface::class => Container\SmtpFactory::class,
                Action\MailAction::class  => Action\MailActionFactory::class,
            ],
        ];
    }
}
