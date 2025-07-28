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

## License
[MIT](./LICENSE.md)
