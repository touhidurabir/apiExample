<?php

/**
 * @file plugins/generic/apiExample/ApiExamplePlugin.php
 *
 * Copyright (c) 2023 Simon Fraser University
 * Copyright (c) 2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ApiExamplePlugin
 *
 * @ingroup plugins_generic_apiExample
 *
 * @brief A simple example plugin to demonstrate 
 */

namespace APP\plugins\generic\apiExample;

use APP\plugins\generic\apiExample\api\v1\users\PKPOverriddenUserController;
use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;

class ApiExamplePlugin extends GenericPlugin
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Load and initialize the plug-in and register plugin hooks.
     *
     * @param string    $category       Name of category plugin was registered to
     * @param string    $path           The path the plugin was found in
     * @param int       $mainContextId  To identify if the plugin is enabled
     *
     * @return bool True/False value by which it's determined if plugin will be registered or not
     */
    public function register($category, $path, $mainContextId = null)
    {
        $success = parent::register($category, $path, $mainContextId);

        if (!$success || !$this->getEnabled()) {
            return $success;
        }
        
        $this->addRoute();

        return $success;
    }

    public function addRoute(): void
    {
        Hook::add('APIHandler::endpoints', function(string $hookName, array $args): bool {

            $apiController = & $args[0]; /** @var \PKP\core\PKPBaseController $apiController */
            $apiHandler = $args[1]; /** @var \PKP\handler\APIHandler $apiHandler */
            
            $apiController = new PKPOverriddenUserController();
            
            return false;
        });
    }

    /**
     * @copydoc Plugin::getDisplayName()
     */
    public function getDisplayName()
    {
        return 'Plugin API Example';
    }

    /**
     * @copydoc Plugin::getDescription()
     */
    public function getDescription()
    {
        return 'Test plugin to demonostrate the usage of API from a plugin.';
    }

}
