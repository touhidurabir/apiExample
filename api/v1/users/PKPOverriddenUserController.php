<?php

/**
 * @file plugins/generic/apiExample/api/v1/users/PKPOverriddenUserController.php
 *
 * Copyright (c) 2023 Simon Fraser University
 * Copyright (c) 2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PKPOverriddenUserController
 *
 * @ingroup plugins_generic_apiExample_api_v1_users
 *
 * @brief Simple exmaple of API controller on plugin level to introduce new API endpoints
 *
 */

namespace APP\plugins\generic\apiExample\api\v1\users;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;
use PKP\API\v1\users\PKPUserController;

class PKPOverriddenUserController extends PKPUserController
{
    /**
     * @copydoc \PKP\core\PKPBaseController::getGroupRoutes()
     */
    public function getGroupRoutes(): void
    {
        parent::getGroupRoutes();

        Route::get('testing/routes/add', $this->addNewRoute(...))
            ->name('user.route.add');
    }
    
    /**
     * A simple test api endpoint which will be added to the list of [users] api endpoint as
     * http://BASE_URL/index.php/CONTEXT_PATH/api/v1/users/testing/routes/add
     */
    public function addNewRoute(Request $illuminateRequest): JsonResponse
    {
        return response()->json([
            'message' => 'A new route added successfully'
        ], Response::HTTP_OK);
    }
}