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
 * @brief   A simple example plugin to demonstrate how to implement API controller 
 *          or extends an existing api endpoint at plugin level so that plugins
 *          can have own api endpoints to tap into the existing collection of
 *          endpoints. 
 */

namespace APP\plugins\generic\apiExample;

use APP\plugins\generic\apiExample\api\v1\users\PKPOverriddenUserController;
use Illuminate\Http\Request as IlluminateRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use PKP\core\PKPBaseController;
use PKP\handler\APIHandler;
use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;
use PKP\security\Role;

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
        
        // add/inject new routes/endpoints to an existing collection/list of api end points
        $this->addRoute();

        return $success;
    }

    /**
     * Add/override new api endpoints to existing list of api endpoints
     */
    public function addRoute(): void
    {
        Hook::add('APIHandler::endpoints::users', function(string $hookName, PKPBaseController &$apiController, APIHandler $apiHandler): bool {
            
            // This allow to add a route on fly without defining a api controller
            // Through this allow quick add/modify routes, it's better to use
            // controller based appraoch which is more structured and understandable
            $apiHandler->addRoute(
                'GET',
                'testing/routes/add/onfly',
                function (IlluminateRequest $request): JsonResponse {
                    return response()->json([
                        'message' => 'A new route added successfully on fly',
                    ], Response::HTTP_OK);
                },
                'test.onfly',
                [
                    Role::ROLE_ID_SITE_ADMIN,
                    Role::ROLE_ID_MANAGER,
                    Role::ROLE_ID_SUB_EDITOR,
                ]
            );
            
            // This allow to update the api controller directly with an overrided controller 
            // that extends a core controller where one or more routes can be added or 
            // multiple existing routes can be modified
            $apiController = new PKPOverriddenUserController();
            
            return false;
        });
    }

    /**
     * @copydoc Plugin::getDisplayName()
     */
    public function getDisplayName()
    {
        return __('plugins.generic.apiExample.displayName');
    }

    /**
     * @copydoc Plugin::getDescription()
     */
    public function getDescription()
    {
        return __('plugins.generic.apiExample.description');
    }

}
