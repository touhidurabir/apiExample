# OJS/OMP/OPS API example at plugin level

Sample test plugin for OJS/OMP/OPS to demonostrate the implementation of new API endpoints or override existing API endpoints from a plugin.

This sample test plugin aims to provide developer an example guide on how to implement API endpoints at plugin level. 

This will be only available and possile if the issue [pkp/pkp-lib#9434](https://github.com/pkp/pkp-lib/issues/9434) get merged into the core. 

## Installation

Git clone the repo into the directory `plugins/generic` of the `OJS/OMP/OPS` installation location and run the following command from the installation installation directory of `OJS/OMP/OPS`

```bash
php lib/pkp/tools/installPluginVersion.php plugins/generic/apiExample/version.xml
```

After the installation completed successfully, make sure to enable the plugin for the intended `context` or for the `site` itself. 


## Testing Plugins API Endpoints

The plugin iteself comes with 2 sample API endpoints . One that introduce a new api endpont at the plugin level and another that tap into the exsting collectio of api endpoint for an entity .

### New API Endpoint : http://BASE_URL/index.php/CONTEXT_PATH/plugins/generic/apiExample/api/v1/tests

The above one introduce a new API endpoint which at the plugin level .

### Override into Existing API : http://BASE_URL/index.php/CONTEXT_PATH/api/v1/users/testing/routes/add

The above which been injected to for the `user` entity at the run time using the [Hook](https://docs.pkp.sfu.ca/dev/documentation/en/utilities-hooks) .


## How to implelemt new API endpoint or Override existing one

Implementing a new API endpoint at the plugin level is the same process of implemeting a new API endpoint in the core application . See the implementation for the [`users`](https://github.com/pkp/pkp-lib/blob/main/api/v1/users/PKPUserController.php) entity for [`OJS`](https://github.com/pkp/ojs/blob/main/api/v1/users/index.php) . Also see the same implementation in [Plugin Code](https://github.com/touhidurabir/apiExample/tree/main/api/v1/tests) .

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

Also see the same implementation in [Plugin Code](https://github.com/touhidurabir/apiExample/blob/main/api/v1/users/PKPOverriddenUserController.php) .


## License
[MIT](./LICENSE.md)
