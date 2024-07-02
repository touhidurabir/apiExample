# OJS/OMP/OPS API example at plugin level

Sample test plugin for OJS/OMP/OPS to demonostrate the implementation of add a new API endpoints or override existing API endpoints from a plugin for an entity.

This sample test plugin aims to provide developer an example guide on how to add/modify API endpoints from a plugin

This will be only available and possile if the issue [pkp/pkp-lib#9434](https://github.com/pkp/pkp-lib/issues/9434) get merged into the core. 

## Installation

Git clone the repo into the directory `plugins/generic` of the `OJS/OMP/OPS` installation location and run the following command from the installation installation directory of `OJS/OMP/OPS`

```bash
php lib/pkp/tools/installPluginVersion.php plugins/generic/apiExample/version.xml
```

After the installation completed successfully, make sure to enable the plugin for the intended `context` or for the `site` itself. 


## Testing Plugins API Endpoints

The plugin iteself comes with 2 sample API endpoints which injected to existing entity. One added through overriding API Controller and another by passing api route details into the `APIHandler::addRoute` method . 

1. http://BASE_URL/index.php/CONTEXT_PATH/api/v1/users/testing/routes/add
2. http://BASE_URL/index.php/CONTEXT_PATH/api/v1/users/testing/routes/add/onfly

it uses the [Hook](https://docs.pkp.sfu.ca/dev/documentation/en/utilities-hooks) mechanism to inject api routes at run time.


## How to implelemt new API endpoint or Override existing one

Implementing a new API endpoint from is the same process of implemeting a new API endpoint in the core application . See the implementation for the [`users`](https://github.com/pkp/pkp-lib/blob/main/api/v1/users/PKPUserController.php) entity for [`OJS`](https://github.com/pkp/ojs/blob/main/api/v1/users/index.php) .

To override a existing API endpoint, need to tap into the `Hook` provided by core service . The API endpoint hook have the following structure as 
```
APIHandler::endpoints::API_ENTITY
```
For example, to tap into and override the API endpoints for `users` entity, need to apply a new `API Controller` that extends the core [`User API Controller`](https://github.com/pkp/pkp-lib/blob/main/api/v1/users/PKPUserController.php) and override the passed API controller in following manner 

```php
Hook::add('APIHandler::endpoints::users', function(string $hookName, PKPBaseController &$apiController, APIHandler $apiHandler): bool {

    $apiController = new PKPOverriddenUserController();
    
    return false;
});
```

Another way to add or override api routes to an existing entity is to use the closure based appraoch to add a route directly in the route collection. 

```php
Hook::add('APIHandler::endpoints::users', function(string $hookName, PKPBaseController &$apiController, APIHandler $apiHandler): bool {

    $apiHandler->addRoute(
        'GET/POST/PUT/PATCH/DELETE', // HTTP Request METHOD
        'some/route/path/to/add',   // The route uri
        function (IlluminateRequest $request): JsonResponse { // The closure/callback of route action handler when the route url got hit
            return response()->json([
                'message' => 'A new route added successfully',
            ], Response::HTTP_OK);
        },
        'name.of.the.route', // Name of the route
        [Role::ROLE_ID_..., Role::ROLE_ID_..., ...] // The route accessable role from `Role::ROLE_ID_*`
    );
    
    return false;
});
```

> NOTE : Above approach to inject route dynamically through the `APIHandler::addRoute` should be used for simple route implementation that handle very simple and limited action logic. For broad and complex route action, better to use the controller based approach.

Also see the same implementation in [Plugin Code](https://github.com/touhidurabir/apiExample/blob/main/api/v1/users/PKPOverriddenUserController.php) .


## License
[MIT](./LICENSE.md)
