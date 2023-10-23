<?php

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
    
    public function addNewRoute(Request $illuminateRequest): JsonResponse
    {
        return response()->json([
            'message' => 'A new route added successfully'
        ], Response::HTTP_OK);
    }
}