<?php

/**
 * Joomla! Content Management System
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Service\Provider;

use Joomla\CMS\Input\Input as CMSInput;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Service provider for the the Joomla Input object.
 *
 * Whilst generally speaking it's an anti-pattern for the request object to be in a DIC, in the case of Joomla whilst
 * there, both the web application class and session class have a hard dependency on the input object, as a result it's
 * required that this exists in the DIC for now. Strategically there should be a long term plan to remove this from the
 * DIC.
 *
 * @note It is strongly recommended that extensions get the input object from the application and DO NOT use this
 * service container.
 *
 * @since  __DEPLOY_VERSION__
 */
class Input implements ServiceProviderInterface
{
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function register(Container $container)
    {
        $container->share(
            CMSInput::class,
            function () {
                return new CMSInput();
            },
            true
        );
    }
}