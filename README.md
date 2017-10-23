<!--h-->
# Structure Manager
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/e4d11f692afc45769893a5299069e643)](https://www.codacy.com/app/laravel-enso/StructureManager?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=laravel-enso/StructureManager&amp;utm_campaign=Badge_Grade)
[![StyleCI](https://styleci.io/repos/95235866/shield?branch=master)](https://styleci.io/repos/95235866)
[![License](https://poser.pugx.org/laravel-enso/structuremanager/license)](https://https://packagist.org/packages/laravel-enso/structuremanager)
[![Total Downloads](https://poser.pugx.org/laravel-enso/structuremanager/downloads)](https://packagist.org/packages/laravel-enso/structuremanager)
[![Latest Stable Version](https://poser.pugx.org/laravel-enso/structuremanager/version)](https://packagist.org/packages/laravel-enso/structuremanager)
<!--/h-->

Structure Manager dependency for [Laravel Enso](https://github.com/laravel-enso/Enso)

### Details

- can be used to more easily insert (default) data, during the install of a package, or later when new routes and permissions are required and can create menus, assign default permissions, etc.
- extends Illuminate's `Migration` class and acts like a migration
- can also rollback its own changes
- when adding menus and permissions, automatic access for the administrator role is added

### Notes

The [Laravel Enso Core](https://github.com/laravel-enso/Core) package comes with this package included.

Depends on:
- depends on [PermissionManager](https://github.com/laravel-enso/PermissionManager) as it uses it for permissions handling
- depends on [MenuManager](https://github.com/laravel-enso/MenuManager) for the creation of menus, when required
- depends on [RoleManager](https://github.com/laravel-enso/RoleManager) for the integration with roles, when adding default permissions

<!--h-->
### Contributions

are welcome. Pull requests are great, but issues are good too.

### License

This package is released under the MIT license.
<!--/h-->