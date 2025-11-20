<?php

/**
 * @file plugins/generic/apiExample/ApiExamplePlugin.php
 *
 * Copyright (c) 2025 Simon Fraser University
 * Copyright (c) 2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ApiExamplePlugin
 *
 * @brief   A simple example plugin to demonstrate how to implement API controller 
 *          or extends an existing api endpoint at plugin level so that plugins
 *          can have own api endpoints to tap into the existing collection of
 *          endpoints. 
 */

namespace APP\plugins\generic\apiExample;

use APP\core\Application;
use APP\plugins\generic\apiExample\CustomApiController;
use Illuminate\Http\Request as IlluminateRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use PKP\core\APIRouter;
use PKP\core\PKPRequest;
use PKP\core\PKPBaseController;
use PKP\handler\APIHandler;
use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;
use PKP\plugins\interfaces\HasAuthorizationPolicy;
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

        // Add a very custom plugin level api end points which are not associated with any entity
        // using the `Dispatcher::dispatch` hook
        // $this->registerPluginCustomRoutes();
        
        // Alternative appraoch to use a new hook to directly inject API controller with auto cehck of path collision
        // using `APIHandler::endpoints::plugin`
        $this->registerPluginApiControllers();

        return $success;
    }

    /**
     * This plugin can be used site-wide or in a specific context.
     * 
     * The isSitePlugin check is used to grant access to different users, so this
     * plugin must return true only if the user is currently in the site-wide
     * context.
     */
    public function isSitePlugin()
    {
        return !Application::get()->getRequest()->getContext();
    }

    /**
     * Add/override new api endpoints to existing list of api endpoints
     */
    public function addRoute(): void
    {
        Hook::add('APIHandler::endpoints::users', function(string $hookName, PKPBaseController $apiController, APIHandler $apiHandler): bool {
            
            // This allow to add a API route on to the existing entity `users` end point as
            // BASE_URL/index.php/CONTEXT_PATH/api/v1/users/testing/routes/add/onfly
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
                ],
                // Optional param to define a set to Authorization Policies for route
                // new class implements HasAuthorizationPolicy
                // {
                //     public function getPolicies(PKPRequest $request, array &$args, array $roleAssignments): array
                //     {
                //         return [
                //             new \PKP\security\authorization\ContextAccessPolicy($request, $roleAssignments)
                //         ];
                //     }
                // }
            );
            
            return Hook::CONTINUE;
        });
    }

    /**
     * Add a new API endpoint which not associated with any entity
     */
    public function registerPluginCustomRoutes(): void
    {
        // Allow to have a custom API endpoint as 
        // BASE_URL/index.php/CONTEXT_PATH/api/v1/custom-plugin-path/
        Hook::add('Dispatcher::dispatch', function (string $hookName, array $args): bool {
            $request = $args[0]; /** @var PKPRequest $request */
            $router = $request->getRouter();

            if (!$router instanceof APIRouter) {
                return Hook::CONTINUE;
            }

            $requestPath = $request->getRequestPath();
            
            if (!str_contains($requestPath, 'custom-plugin-path')) {
                return Hook::CONTINUE;
            }

            $controller = new CustomApiController;
            $handler = new APIHandler($controller);

            // we can get all the registered routes at this point as if need to run any more extra checks
            // app('router')->getRoutes();

            $router->setHandler($handler);
            $handler->runRoutes();

            return Hook::ABORT;
        });
    }

    /**
     * Add a new API endpoint which not associated with any entity
     * 
     * This allow to register multiple API controller at a time with checks that no plugin api has
     * extact same handler path of `PKPBaseController::getHandlerPath()` to avoid same path collision
     */
    public function registerPluginApiControllers(): void
    {
        Hook::add('APIHandler::endpoints::plugin', function (string $hookName, APIRouter $apiRouter): bool {
            $apiRouter->registerPluginApiControllers([
                // Allow to have a custom API endpoint as 
                // BASE_URL/index.php/CONTEXT_PATH/api/v1/custom-plugin-path/
                new CustomApiController,

                // Allow to have a custom ADMIN API endpoint as 
                // BASE_URL/index.php/index/api/v1/custom-admin-plugin-path/
                new CustomAdminApiController,
            ]);

            return Hook::CONTINUE;
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
