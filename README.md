# OJS/OMP/OPS API example at plugin level

Sample test plugin for OJS/OMP/OPS to demonostrate the implementation of adding a new API endpoints through a plugin for an entity.

## Installation

Git clone the repo into the directory `plugins/generic` of the `OJS/OMP/OPS` installation location and run the following command from the installation installation directory of `OJS/OMP/OPS`

```bash
php lib/pkp/tools/installPluginVersion.php plugins/generic/apiExample/version.xml
```

After the installation completed successfully, make sure to enable the plugin for the intended `context` or for the `site` itself. 


## Testing Plugins API Endpoints

The plugin iteself comes with a sample API endpoints which injected to existing entity `users` by passing api route details into the `APIHandler::addRoute` method as follow :

`http://BASE_URL/index.php/CONTEXT_PATH/api/v1/users/testing/routes/add/onfly`

it uses the [Hook](https://docs.pkp.sfu.ca/dev/documentation/en/utilities-hooks) mechanism to inject api routes at run time.


## How to implelemt new API endpoint or Override existing one

To add a new API endpoint/route, need to tap into the `Hook` provided by core service . The API endpoint hook have the following structure as 
```
APIHandler::endpoints::API_ENTITY
```

For example, to tap into and override the API endpoints for `users` entity, need to use the closure based appraoch to add a new route directly in the existing route collection. 

```php
use PKP\core\PKPBaseController;
use PKP\handler\APIHandler;
use PKP\plugins\Hook;
use Illuminate\Http\Request as IlluminateRequest;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

Hook::add('APIHandler::endpoints::users', function(string $hookName, PKPBaseController $apiController, APIHandler $apiHandler): bool {

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
    
    return Hook::CONTINUE;
});
```

It is also possible to define a set of authorization policies for a route register from a plugin. Considering above example, we can pass a optional final param which must implements the contract `PKP\plugins\interfaces\HasAuthorizationPolicy` as follow 

```php
use PKP\core\PKPRequest;
use PKP\plugins\interfaces\HasAuthorizationPolicy;

Hook::add('APIHandler::endpoints::users', function(string $hookName, PKPBaseController $apiController, APIHandler $apiHandler): bool {

    $apiHandler->addRoute(
        // all the required params as above ...

        // Optional param to define a set to Authorization Policies for route
        new class implements HasAuthorizationPolicy
        {
            public function getPolicies(PKPRequest $request, array &$args, array $roleAssignments): array
            {
                return [
                    // new \PKP\security\authorization\ContextAccessPolicy($request, $roleAssignments),
                    // more policies
                ];
            }
        }
    );
    
    return Hook::CONTINUE;
});
```

Note that in the return array from method `getPolicies` must contains the instances of only `PKP\security\authorization\AuthorizationPolicy` and `PKP\security\authorization\PolicySet`, as exception will be thrown for any other instances type .

It is also possible for plugins to have it's very own custom api routes instead of appendings new ones
to the currently existing paths. This can be accomplished using the hook **APIHandler::endpoints::plugin** in the following manner
```php
use PKP\plugins\Hook;
use PKP\core\APIRouter;

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
```
Here the method `APIRouter::registerPluginApiControllers` will take array of api controller instance
where each api controller instances must be an instance of `PKP\core\PKPBaseController`.

Important point to note that `APIRouter::registerPluginApiControllers` will internally run a check against already registered plugin's custom api controller/endpoints to verify the uniqueness the
given path prefix and will throw exception is there is already exists one. This is done to prevent
the plugin custom API path collision and leaking one plugins response/data to another. 

## Custom plugin routes via `Dispatcher::dispatch` (advanced, not recommended)

As a last-resort fallback, a plugin can register a completely custom API route by hooking into the `Dispatcher::dispatch` hook and wiring up the `APIHandler` manually. This bypasses both the entity (`APIHandler::endpoints::API_ENTITY`) and the plugin-controller (`APIHandler::endpoints::plugin`) hooks shown above.

> **Only reach for this when none of the approaches above can satisfy your need.** It trades away the safety the supported hooks give you, and the failure modes can be hard to trace.

See `ApiExamplePlugin::registerPluginCustomRoutes()` for the working example:

```php
use PKP\plugins\Hook;
use PKP\core\PKPRequest;
use PKP\core\APIRouter;
use PKP\handler\APIHandler;

Hook::add('Dispatcher::dispatch', function (string $hookName, array $args): bool {
    $request = $args[0]; /** @var PKPRequest $request */
    $router = $request->getRouter();

    // Only act on API requests
    if (!$router instanceof APIRouter) {
        return Hook::CONTINUE;
    }

    // You must match the path yourself — there is no routing help here
    if (!str_contains($request->getRequestPath(), 'custom-plugin-path')) {
        return Hook::CONTINUE;
    }

    $controller = new CustomApiController;
    $handler = new APIHandler($controller);

    $router->setHandler($handler);
    $handler->runRoutes();

    return Hook::ABORT;
});
```

### Why this is discouraged

- **No path-collision check.** Unlike `APIRouter::registerPluginApiControllers()`, nothing verifies that your path is unique. You match the request path by hand, so you can silently shadow another plugin's (or a core) endpoint and leak one plugin's response/data into another.
- **Runs before the context schema is (re)loaded.** `Dispatcher::dispatch` fires earlier in the request lifecycle than the API hooks — before the context schema is reloaded and before routing happens. A controller registered this way that reads the context schema or any context-specific data may see stale or incomplete data and behave unexpectedly.
- **You bypass normal API routing.** You manually construct the `APIHandler`, attach it to the router and call `runRoutes()`, then return `Hook::ABORT` to stop further dispatch. This skips the safeguards the supported hooks provide and is easy to get subtly wrong.
- **Possibly other surprises.** Because it sidesteps the normal flow, this approach *may* produce other hard-to-diagnose behaviour (not necessarily, but it is not guaranteed safe). Prefer `APIHandler::endpoints::plugin`; only use `Dispatcher::dispatch` if you truly have no other option, and then test thoroughly.

## License
[MIT](./LICENSE.md)
