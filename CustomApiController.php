<?php

/**
 * @file plugins/generic/apiExample/CustomApiController.php
 *
 * Copyright (c) 2025 Simon Fraser University
 * Copyright (c) 2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class CustomApiController
 *
 * @brief Custom API controller with plugin level endpoints
 */

namespace APP\plugins\generic\apiExample;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PKP\core\PKPBaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

class CustomApiController extends PKPBaseController
{
    /**
     * @copydoc \PKP\core\PKPBaseController::getHandlerPath()
     */
    public function getHandlerPath(): string
    {
        return 'custom-plugin-path';
    }

    /**
     * @copydoc \PKP\core\PKPBaseController::getRouteGroupMiddleware()
     */
    public function getRouteGroupMiddleware(): array
    {
        return [
            'has.user',
            'has.context',
        ];
    }

    /**
     * @copydoc \PKP\core\PKPBaseController::getGroupRoutes()
     */
    public function getGroupRoutes(): void
    {
        // Route : BASE_URL/index.php/CONTEXT_PATH/api/v1/custom-plugin-path/
        Route::get('', $this->getData(...))->name('api.example.custom.getData');

        // Route : BASE_URL/index.php/CONTEXT_PATH/api/v1/custom-plugin-path/
        Route::post('', $this->postData(...))->name('api.example.custom.postData');
    }

    /**
     * Handle GET requests for the custom API endpoint.
     */
    public function getData(Request $illuminateRequest): JsonResponse
    {
        return response()->json(['message' => 'This is a GET response'], Response::HTTP_OK);
    }

    /**
     * Handle POST requests for the custom API endpoint.
     */
    public function postData(Request $illuminateRequest): JsonResponse
    {
        return response()->json(['message' => 'This is a POST response'], Response::HTTP_OK);
    }
}
