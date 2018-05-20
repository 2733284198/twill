# Twill

## Introduction

Twill is a Laravel Composer package to rapidly create and deploy a completely custom admin area for our clients websites that is highly functional, beautiful and easy to use.
It's a curation of all the features that were developed on custom admin areas since our switch to Laravel in 2014. The architecture, conventions and helpers it provides currently powers the [Opéra National de Paris 2015 website redesign](https://www.operadeparis.fr/), the [AREA 17 2016 website redesign](https://area17.com) and the [THG 2016 redesign website](https://www.thg-paris.com/). Initially released in December 2016, this Laravel package is powering the [Roto](https://roto.com), [Mai 36](https://mai36.com) and [Pentagram](https://www.pentagram.com) 2017 redesigns, as well as the [Artists at Risk Connection](https://artistsatriskconnection.org) platform and Sonia Rykiel's [Rykielism](https://rykielism.soniarykiel.com). Translation, Art Institute of Chicago, Charvet, OSF Guides and AREA 17 Guides are currently being built or about to launch using this package as a dependency to build their CMS.

It provides a beautiful admin interface that really focuses on the editors needs, using AREA 17's custom built Vue.js components and a vast number of pre-built features to focus on building fully custom forms instead of rebuilding the same thing over and over:
- user authentication, authorization and management
- rapid searching and editing of content for editors with various tools and options:
  - search / filters / sort
  - quick publish / feature / reorder / edit / delete
  - input, textarea, rich textarea form fields with optional SEO optimized limits
  - date pickers
  - select, multi-select, content type browser for related content and tags
  - image selector with cropping
  - flexible content block editor (composable blocks from Vue components)
  - form repeaters
  - translated fields with independent publication status
  - slugs management that allows you to automatically redirect old urls
  - content versioning with preview and side by side comparison of fully rendered frontend site
- intuitive content featuring using a buckets UI (put any of your content types in "buckets" to manage any layout of featured content)
- a media library:
  - with S3 or local storage
  - powered by Imgix rendering for on the fly resizing, cropping and compression of responsive images
  - easily extendable to support other storage and/or rendering providers (ie. Cloudinary, Croppa, IIIF, ...)
  - alternative text and caption attached to each image, changeable when attached to a form too
  - bulk tagging for easier filtering of image collection
- a file library:
  - with S3 or local storage
  - easily extendable to support other storage providers
  - can be used to attach and serve pdfs or videos (or any file...) in any content type
- the ability to art direct responsive images through:
  - different manual cropping ratio for each breakpoints
  - entropy or faces cropping with no manual input (when using with Imgix or another image service supporting this)
- rapid new content types creation/edition/maintenance for developers (generators and conventions for unified CRUD features)
- development and production ready toolset (debug bar, inspector, exceptions handler)
- static templates automatic routing (ie: adding a blade file at a certain location will be automatically available at the same url of its filename, no need to deal with application code, nice for frontend devs building statics before backend devs starts)

In development, you can use it in any Laravel environment like [Valet](https://laravel.com/docs/5.6/valet) or [Homestead](https://laravel.com/docs/5.6/homestead), though in a client's project context, you would ideally run your application in a custom virtual machine or Docker environment that is as close as possible as your production environment (either through a custom `after.sh` config for Homestead, an Ansible provisionned Vagrant box or a Docker Compose project, for example).

## Install

This is a private package hosted on [code.area17.com](https://code.area17.com) for now, so you need to add the following to your `composer.json` file before installing:

```json
"repositories": [
    {
        "type": "git",
        "url": "git@code.area17.com:a17/laravel-cms-toolkit.git"
    }
],
```

Then you should be able to run:

```bash
composer require a17/laravel-cms-toolkit
```
Add Twill Install service provider in `config/app.php` (before Application Service Providers):

```php
<?php

'providers' => [
    ...
    A17\Twill\TwillInstallServiceProvider::class,
];
```

Setup your `.env` file:

```bash
# APP_URL without scheme so that the package can resolve admin.APP_URL automatically
# Your computer should be able to resolve both APP_URL and admin.APP_URL
# For example, with a vagrant vm you should add to your /etc/hosts file:
# 192.168.10.10 APP_URL
# 192.168.10.10 admin.APP_URL
APP_URL=client.dev.a17.io 

# Optionnaly, you can specify the admin url yourself 
#ADMIN_APP_URL=client.dev.a17.io
# as well as a path if you want to show the admin on the same domain as your app
#ADMIN_APP_PATH=admin

# When running on 2 different subdomains (which is the default configuration), you might want to share cookies 
# between both so that CMS users can access drafts on the frontend
#SESSION_DOMAIN=.client.dev.a17.io

# If you use S3 uploads, you'll need those credentials
#AWS_KEY=client_aws_key
#AWS_SECRET=client_aws_secret
#AWS_BUCKET=client_bucket
#AWS_USE_HTTPS=true

# If you use Imgix, you'll need a source url
#IMGIX_SOURCE_HOST=client.imgix.net
#IMGIX_USE_SIGNED_URLS=false
#IMGIX_USE_HTTPS=true

# Delete uploaded files when deleting from media library UI
#MEDIA_LIBRARY_CASCADE_DELETE=true

# Needed only if you use a map form field
#GOOGLE_MAPS_API_KEY=
```

Run the install command

```bash
php artisan twill:install
```

Run the setup command (it will migrate your database schema so run it where your database is accessible, ie. in vagrant)

```bash
php artisan twill:setup
```

Setup your list of available languages for translated fields in `config/translatable.php` (without nested locales).

```php
<?php

return [
    'locales' => [
        'en',
        'fr',
    ],
```

Use a single locale code if you're not using model translations in your project.

Next, let's setup the CMS frontend toolset composed of NPM scripts.

Add the following npm scripts to your project's `package.json`:

```json
"scripts": {
  "cms-build": "npm run cms-copy-blocks && cd vendor/a17/laravel-cms-toolkit && npm ci && npm run prod && cp -R public/ ${INIT_CWD}/public",
  "cms-copy-blocks": "npm run cms-clean-blocks && mkdir -p resources/assets/js/blocks/ && mkdir -p vendor/a17/laravel-cms-toolkit/frontend/js/components/blocks/customs/ && cp -R resources/assets/js/blocks/ vendor/a17/laravel-cms-toolkit/frontend/js/components/blocks/customs/",
  "cms-clean-blocks": "rm -rf vendor/a17/laravel-cms-toolkit/frontend/js/components/blocks/customs/*"
}
```

Finally, add the following to your project `.gitignore` if you don't want to put CMS compiled assets in Git:
```
public/assets/admin
public/mix-manifest.json
public/hot
```

You'll want to run `npm run cms-build` to start working locally as well on a production server.

If you are working on blocks, or contributing to this project, and would like to use Hot Module Reloading to propagate your changes when recompiling blocks or contributing to the toolkit itself, use `npm run cms-dev`. You'll need to install the following dev dependencies to your project's `package.json`:

```json
"devDependencies": {
    "concurrently": "^3.5.1",
    "watch": "^1.0.2"
}
```

And the following npm scripts: 

```json
"scripts": {
  "cms-dev": "mkdir -p vendor/a17/laravel-cms-toolkit/public npm run cms-copy-blocks && concurrently \"cd vendor/a17/laravel-cms-toolkit && npm ci && npm run hot\" \"npm run cms-watch\" && npm run cms-clean-blocks",
  "cms-watch": "concurrently \"watch 'npm run cms-hot' vendor/a17/laravel-cms-toolkit/public --wait=2 --interval=0.1\" \"npm run cms-watch-blocks\"",
  "cms-hot": "cd vendor/a17/laravel-cms-toolkit && cp -R public/ ${INIT_CWD}/public",
  "cms-watch-blocks": "watch 'npm run cms-copy-blocks' resources/assets/js/blocks --wait=2 --interval=0.1"
}
```

That's about it!

## Usage

### Static templates
Frontenders, you might often be the first users of this package in new Laravel apps when starting to work on static templates.

Creating Blade files in `views/templates` will make them directly accessible at `admin.domain.dev/templates/file-name`.

Feel free to use all [Blade](https://laravel.com/docs/5.3/blade) features, extend a parent layout and cut out your views in partials, this will help a lot during integration.

Frontend assets should live in the `public/dist` folder along with a `rev-manifest.json` for compiled assets in production. Using the [A17 FE Boilerplate](https://code.area17.com/a17/fe-boilerplate) should handle that for you.

Use the `revAsset('asset.{css|js})` helper in your templates to get assets URLs in any environment.

Use the `icon('icon-name', [])` helper to display an icon from the SVG sprite. The second parameter is an array of options. It currently understand `title`, `role` and `css_class`.


### Configuration
#### Options

By default, you shouldn't have to modify anything if you want to use the default config which is basically:
- users management
- media library on S3 with Imgix
- file library on S3

The only thing you would have to do is setting up the necessary environment variables in your `.env` file.

You can override any of these configurations values independendtly from the empty `config/twill.php` file that was published in your app when you ran the `twill:install` command.

```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Namespace
    |--------------------------------------------------------------------------
    |
    | This value is the namespace of your application.
    |
     */
    'namespace' => 'App',

    /*
    |--------------------------------------------------------------------------
    | Application Admin URL
    |--------------------------------------------------------------------------
    |
    | This value is the URL of your admin application.
    |
     */
    'admin_app_url' => env('ADMIN_APP_URL', 'admin.' . env('APP_URL')),
    'admin_app_path' => env('ADMIN_APP_PATH', ''),

    /*
    |--------------------------------------------------------------------------
    | Twill Enabled Features
    |--------------------------------------------------------------------------
    |
    | This array allows you to enable/disable the Twill default features.
    |
     */
    'enabled' => [
        'users-management' => true,
        'media-library' => true,
        'file-library' => true,
        'block-editor' => true,
        'buckets' => false,
        'users-image' => false,
        'site-link' => false,
        'settings' => false,
        'google-login' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Twill Auth configuration
    |--------------------------------------------------------------------------
    |
    | Right now this only allows you to redefine the
    | default login redirect path.
    |
     */
    'auth_login_redirect_path' => '/',

    /*
    |--------------------------------------------------------------------------
    | Twill Media Library configuration
    |--------------------------------------------------------------------------
    |
    | This array allows you to provide the package with your configuration
    | for the media library disk, endpoint type and others options depending
    | on your endpoint type.
    |
    | Supported endpoint types: 'local' and 's3'.
    | Set cascade_delete to true to delete files on the storage too when
    | deleting from the media library.
    | If using the 'local' endpoint type, define a 'local_path' to store files.
    | Supported image service: 'A17\Twill\Services\MediaLibrary\Imgix'
    |
     */
    'media_library' => [
        'disk' => 'libraries',
        'endpoint_type' => env('MEDIA_LIBRARY_ENDPOINT_TYPE', 's3'),
        'cascade_delete' => env('MEDIA_LIBRARY_CASCADE_DELETE', false),
        'local_path' => env('MEDIA_LIBRARY_LOCAL_PATH'),
        'image_service' => env('MEDIA_LIBRARY_IMAGE_SERVICE', 'A17\Twill\Services\MediaLibrary\Imgix'),
        'acl' => env('MEDIA_LIBRARY_ACL', 'private'),
        'filesize_limit' => env('MEDIA_LIBRARY_FILESIZE_LIMIT', 50),
        'allowed_extensions' => ['svg', 'jpg', 'gif', 'png', 'jpeg'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Twill Imgix configuration
    |--------------------------------------------------------------------------
    |
    | This array allows you to provide the package with your configuration
    | for the Imgix image service.
    |
     */
    'imgix' => [
        'source_host' => env('IMGIX_SOURCE_HOST'),
        'use_https' => env('IMGIX_USE_HTTPS', true),
        'use_signed_urls' => env('IMGIX_USE_SIGNED_URLS', false),
        'sign_key' => env('IMGIX_SIGN_KEY'),
        'default_params' => [
            'fm' => 'jpg',
            'q' => '80',
            'auto' => 'compress,format',
            'fit' => 'min',
        ],
        'lqip_default_params' => [
            'fm' => 'gif',
            'auto' => 'compress',
            'blur' => 100,
            'dpr' => 1,
        ],
        'social_default_params' => [
            'fm' => 'jpg',
            'w' => 900,
            'h' => 470,
            'fit' => 'crop',
            'crop' => 'entropy',
        ],
        'cms_default_params' => [
            'q' => 60,
            'dpr' => 1,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Twill File Library configuration
    |--------------------------------------------------------------------------
    |
    | This array allows you to provide the package with your configuration
    | for the file library disk, endpoint type and others options depending
    | on your endpoint type.
    |
    | Supported endpoint types: 'local' and 's3'.
    | Set cascade_delete to true to delete files on the storage too when
    | deleting from the file library.
    | If using the 'local' endpoint type, define a 'local_path' to store files.
    |
     */
    'file_library' => [
      'disk' => 'libraries',
      'endpoint_type' => env('FILE_LIBRARY_ENDPOINT_TYPE', 's3'),
      'cascade_delete' => env('FILE_LIBRARY_CASCADE_DELETE', false),
      'local_path' => env('FILE_LIBRARY_LOCAL_PATH'),
      'file_service' => env('FILE_LIBRARY_FILE_SERVICE', 'A17\Twill\Services\FileLibrary\Disk'),
      'acl' => env('FILE_LIBRARY_ACL', 'public-read'),
      'filesize_limit' => env('FILE_LIBRARY_FILESIZE_LIMIT', 50),
      'allowed_extensions' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Twill Block Editor configuration
    |--------------------------------------------------------------------------
    |
    | This array allows you to provide the package with your configuration
    | for the Block Editor form field.
    |
     */
    'block_editor' => [
        'block_single_layout' => 'site.layouts.block',
        'block_views_path' => 'site.blocks',
        'block_views_mappings' => [],
        'block_preview_render_childs' => true,
        'blocks' => [
            'title' => [
                'title' => 'Title',
                'icon' => 'text',
                'component' => 'a17-block-title',
            ],
            'image' => [
                'title' => 'Image',
                'icon' => 'image',
                'component' => 'a17-block-image',
            ],
            'product' => [
                'title' => 'Product',
                'icon' => 'text',
                'component' => 'a17-block-products',
            ],
            'color' => [
                'title' => 'Colors',
                'icon' => 'image',
                'component' => 'a17-block-color',
            ],
        ],
        'repeaters' => [
            'colors' => [
                'title' => 'Colors',
                'trigger' => 'Add colors',
                'component' => 'a17-block-colors',
                'max' => 20,
            ],
        ],
        'crops' => [
            'media' => [
                'desktop' => [
                    [
                        'name' => 'desktop',
                        'ratio' => 16/9,
                    ],
                ],
                'mobile' => [
                    [
                        'name' => 'mobile',
                        'ratio' => 1,
                    ],
                ],
            ],
        ],
        'browser_route_prefixes' => [
            'products' => 'content',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Twill SEO configuration
    |--------------------------------------------------------------------------
    |
    | This array allows you to provide the package with some SEO configuration
    | for the frontend site controller helper and image service.
    |
     */
    'seo' => [
        'site_title' => config('app.name'),
        'site_desc' => config('app.name'),
        'image_default_id' => env('SEO_IMAGE_DEFAULT_ID'),
        'image_local_fallback' => env('SEO_IMAGE_LOCAL_FALLBACK'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Twill Developer configuration
    |--------------------------------------------------------------------------
    |
    | This array allows you to enable/disable debug tool and configurations.
    |
     */
    'debug' => [
        'use_whoops' => env('DEBUG_USE_WHOOPS', true),
        'whoops_path_guest' => env('WHOOPS_GUEST_PATH'),
        'whoops_path_host' => env('WHOOPS_HOST_PATH'),
        'use_inspector' => env('DEBUG_USE_INSPECTOR', false),
        'debug_bar_in_fe' => env('DEBUG_BAR_IN_FE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Twill Frontend assets configuration
    |--------------------------------------------------------------------------
    |
    | This allows you to setup frontend helpers related settings.
    |
    |
     */
    'frontend' => [
        'rev_manifest_path' => public_path('dist/rev-manifest.json'),
        'dev_assets_path' => '/dist',
        'dist_assets_path' => '/dist',
        'svg_sprites_path' => 'sprites.svg', // relative to dev/dist assets paths
        'svg_sprites_use_hash_only' => true,
        'views_path' => 'site',
        'home_route_name' => 'home',
    ],
];

```


#### Navigation

This file manages the navigation of your admin area. Using the CMS UI, the package provides 2 levels of navigation: global and primaryy. This file simply contains a nested array description of your navigation.

Each entry is defined by multiple options.
The simplest entry has a `title` and a `route` option which is a Laravel route name. A global entry can define a `primary_navigation` array that will contains more entries.

Two other options are provided that are really useful in conjunction with the CRUD modules you'll create in your application: `module` and `can`. `module` is a boolean to indicate if the entry is routing to a module route. By default it will link to the index route of the module you used as your entry key. `can` allows you to display/hide navigation links depending on the current user and permission name you specify.

Example:

```php
<?php

return [
    'dashboard' => [
        'title' => 'Dashboard',
        'route' => 'admin.dashboard',
    ],
    'work' => [
        'title' => 'Work',
        'route' => 'admin.work.projects.index',
        'primary_navigation' => [
            'projects' => [
                'title' => 'Projects',
                'module' => true,
            ],
            'clients' => [
                'title' => 'Clients',
                'module' => true,
            ],
            'industries' => [
                'title' => 'Industries',
                'module' => true,
            ],
            'studios' => [
                'title' => 'Studios',
                'module' => true,
            ],
        ],
    ],
];
```

To make it work properly and to get active states automatically, you should structure your routes in the same way using for example here:

```php
<?php

Route::get('/dashboard')->...->name('admin.dashboard');
Route::group(['prefix' => 'work'], function () {
    Route::module('projects');
    Route::module('clients');
    Route::module('industries');
    Route::module('studios');
});
```

### CRUD Modules
#### CLI Generator
You can generate all the files needed for a new CRUD using the generator:

```bash
php artisan twill:module yourPluralModuleName
```

The command has a couple of options :
- `--hasBlocks (-B)`,
- `--hasTranslation (-T)`,
- `--hasSlug (-S)`,
- `--hasMedias (-M)`,
- `--hasFiles (-F)`,
- `--hasPosition (-P)`
- `--hasRevisions(-R)`.

It will generate a migration file, a model, a repository, a controller, a form request object and a form view.

Start by filling in the migration and models.

Add `Route::module('yourPluralModuleName}');` to your admin routes file.

Setup a new CMS menu item in `config/cms-navigation.php`.

Setup your index options and columns in your controller.

Setup your form fields in `resources/views/admin/moduleName/form.blade.php`.

Enjoy.

#### Routes

A router macro is available to create module routes quicker:
```php
<?php

Route::module('yourModulePluralName');

// You can add an array of only/except action names as a second parameter
// By default, the following routes are created : 'reorder', 'publish', 'browser', 'bucket', 'feature', 'restore', 'bulkFeature', 'bulkPublish', 'bulkDelete', 'bulkRestore'
Route::module('yourModulePluralName', ['except' => ['reorder', 'feature', 'bucket', 'browser']])

// You can add an array of only/except action names for the resource controller as a third parameter
// By default, the following routes are created : 'index', 'store', 'show', 'edit', 'update', 'destroy'
Route::module('yourModulePluralName', [], ['only' => ['index', 'edit', 'store', 'destroy']])

// The last optional parameter disable the resource controller actions on the module
Route::module('yourPluralModuleName', [], [], false)
```

#### Migrations
Migrations are regular Laravel migrations. A few helpers are available to create the default fields any CRUD module will use:

```php
<?php

// main table, holds all non translated fields
Schema::create('table_name_plural', function (Blueprint $table) {
    createDefaultTableFields($table)
    // will add the following inscructions to your migration file
    // $table->increments('id');
    // $table->softDeletes();
    // $table->timestamps();
    // $table->boolean('published');
});

// translation table, holds translated fields
Schema::create('table_name_singular_translations', function (Blueprint $table) {
    createDefaultTranslationsTableFields($table, 'tableNameSingular')
    // will add the following inscructions to your migration file
    // createDefaultTableFields($table);
    // $table->string('locale', 6)->index();
    // $table->boolean('active');
    // $table->integer("{$tableNameSingular}_id")->unsigned();
    // $table->foreign("{$tableNameSingular}_id", "fk_{$tableNameSingular}_translations_{$tableNameSingular}_id")->references('id')->on($table)->onDelete('CASCADE');
    // $table->unique(["{$tableNameSingular}_id", 'locale']);
});

// slugs table, holds slugs history
Schema::create('table_name_singular_slugs', function (Blueprint $table) {
    createDefaultSlugsTableFields($table, 'tableNameSingular')
    // will add the following inscructions to your migration file
    // createDefaultTableFields($table);
    // $table->string('slug');
    // $table->string('locale', 6)->index();
    // $table->boolean('active');
    // $table->integer("{$tableNameSingular}_id")->unsigned();
    // $table->foreign("{$tableNameSingular}_id", "fk_{$tableNameSingular}_translations_{$tableNameSingular}_id")->references('id')->on($table)->onDelete('CASCADE')->onUpdate('NO ACTION');
});

// revisions table, holds revision history
Schema::create('table_name_singular_revisions', function (Blueprint $table) {
    createDefaultRevisionTableFields($table, 'tableNameSingular');
    // will add the following inscructions to your migration file
    // $table->increments('id');
    // $table->timestamps();
    // $table->json('payload');
    // $table->integer("{$tableNameSingular}_id")->unsigned()->index();
    // $table->integer('user_id')->unsigned()->nullable();
    // $table->foreign("{$tableNameSingular}_id")->references('id')->on("{$tableNamePlural}")->onDelete('cascade');
    // $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
});

// related content table, holds many to many association between 2 tables
Schema::create('table_name_singular1_table_name_singular2', function (Blueprint $table) {
    createDefaultRelationshipTableFields($table, $table1NameSingular, $table2NameSingular)
    // will add the following inscructions to your migration file 
    // $table->integer("{$table1NameSingular}_id")->unsigned();
    // $table->foreign("{$table1NameSingular}_id")->references('id')->on($table1NamePlural)->onDelete('cascade');
    // $table->integer("{$table2NameSingular}_id")->unsigned();
    // $table->foreign("{$table2NameSingular}_id")->references('id')->on($table2NamePlural)->onDelete('cascade');
    // $table->index(["{$table2NameSingular}_id", "{$table1NameSingular}_id"]);
});
```

A few CRUD controllers require that your model have a field in the database with a specific name: `published`, `publish_start_date`, `publish_end_date`, `public`, and `position`, so stick with those column names if you are going to use publication status, timeframe and reorderable listings.


#### Models

Set your fillables to prevent mass-assignement. Very important as we use `request()->all()` in the module controller.


For fields that should always be saved as null in the database when not sent by the form, use the `nullable` array.

For fields that should always be saved to false in the database when not sent by the form, use the `checkboxes` array. The `published` field is a good example.

Depending on the features you need on your model, include the availables traits and configure their respective options:

- HasPosition: implement the `A17\Twill\Models\Behaviors\Sortable` interface and add a position field to your fillables.

- HasTranslation: add translated fields in the `translatedAttributes` array and in the `fillable` array of the generated translatable model in `App/Models/Translations` (always keep the `active` and `locale` fields).

- HasSlug: specify the field(s) that is going to be used to create the slug in the `slugAttributes` array

- HasMedias: add the `mediasParams` configuration array:

```php
<?php

public $mediasParams = [
    'cover' => [ // role name
        'default' => [ // crop name
            [
                'name' => 'default', // ratio name, same as crop name if single
                'ratio' => 16 / 9, // ratio as a fraction or number
            ],
        ],
        'mobile' => [
            [
                'name' => 'landscape', // ratio name, multiple allowed
                'ratio' => 16 / 9, 
            ],
            [
                'name' => 'portrait', // ratio name, multiple allowed
                'ratio' => 3 / 4,
            ],
        ],
    ],
    '...' => [ // another role
        ... // with crops
    ]
];
```

- HasFiles: add the `filesParams` configuration array

```php
<?php

public $filesParams = ['project_pdf']; // a list of file roles
```

- HasRevisions: no options

#### Controllers

```php
<?php

    protected $moduleName = 'yourModuleName';
    
    /*
     * Options of the index view
     */
    protected $indexOptions = [
        'create' => true,
        'edit' => true,
        'publish' => true,
        'bulkPublish' => true,
        'feature' => false,
        'bulkFeature' => false,
        'restore' => true,
        'bulkRestore' => true,
        'delete' => true,
        'bulkDelete' => true,
        'reorder' => false,
        'permalink' => true,
        'bulkEdit' => true,
        'editInModal' => false,
    ];

    /*
     * Key of the index column to use as title/name/anythingelse column
     * This will be the first column in the listing and will have a link to the form
     */
    protected $titleColumnKey = 'title';
    
    /*
     * Available columns of the index view
     */
    protected $indexColumns = [
        'image' => [
            'thumb' => true, // image column
            'variant' => [
                'role' => 'cover',
                'crop' => 'default',
            ],
        ],
        'title' => [ // field column
            'title' => 'Title',
            'field' => 'title',
        ],
        'subtitle' => [
            'title' => 'Subtitle',
            'field' => 'subtitle',
            'sort' => true, // column is sortable
            'visible' => false, // will be available from the columns settings dropdown
        ],
        'relationName' => [ // relation column
            'title' => 'Relation name',
            'sort' => true,
            'relationship' => 'relationName',
            'field' => 'relationFieldToDisplay'
        ],
        'presenterMethodField' => [ // presenter column
            'title' => 'Field title',
            'field' => 'presenterMethod',
            'present' => true,
        ]
    ];

    /*
     * Columns of the browser view for this module when browsed from another module
     * using a browser form field
     */
    protected $browserColumns = [
        'title' => [
            'title' => 'Title',
            'field' => 'title',
        ],
    ];

    /*
     * Relations to eager load for the index view
     */
    protected $indexWith = [];

    /*
     * Relations to eager load for the form view
     * Add relationship used in multiselect and resource form fields
     */
    protected $formWith = [];

    /*
     * Relation count to eager load for the form view
     */
    protected $formWithCount = [];

    /*
     * Filters mapping ('filterName' => 'filterColumn')
     * You can associate items list to filters by having a filterNameList key in the indexData array
     * For example, 'category' => 'category_id' and 'categoryList' => app(CategoryRepository::class)->listAll()
     */
    protected $filters = [];

    /*
     * Add anything you would like to have available in your module's index view
     */
    protected function indexData($request)
    {
        return [];
    }

    /*
     * Add anything you would like to have available in your module's form view
     * For example, relationship lists for multiselect form fields
     */
    protected function formData($request)
    {
        return [];
    }

    // Optional, if the automatic way is not working for you (default is ucfirst(str_singular($moduleName)))
    protected $modelName = 'model';

    // Optional, to specify a different feature field name than the default 'featured'
    protected $featureField = 'featured';

    // Optional, specify number of items per page in the listing view (-1 to disable pagination)
    protected $perPage = 20;

    // Optional, specify the default listing order
    protected $defaultOrders = ['title' => 'asc'];

    // Optional, specify the default listing filters
    protected $defaultFilters = ['search' => 'title|search'];
```

You can also override all actions and internal functions, checkout the ModuleController source in `A17\Twill\Http\Controllers\Admin\ModuleController`.

#### Form Requests
Classic Laravel 5 [form request validation](https://laravel.com/docs/5.5/validation#form-request-validation).

You can choose to use different rules for creation and update by implementing the following 2 functions instead of the classic `rules` one :

```php
<?php

public function rulesForCreate()
{
    return [];
}

public function rulesForUpdate()
{
    return [];
}
```

There is also an helper to define rules for translated fields without having to deal with each locales:

```php
<?php

$this->rulesForTranslatedFields([
 // regular rules
], [
  // translated fields rules with just the field name like regular rules
]);
```

There is also an helper to define validation messages for translated fields:

```php
<?php

$this->messagesForTranslatedFields([
 // regular messages
], [
  // translated fields messages
]);
```


#### Repositories

Depending on the model feature, include one or multiple of those traits: `HandleTranslations`, `HandleSlugs`, `HandleMedias`, `HandleFiles`, `HandleRevisions`, `HandleBlocks`, `HandleRepeaters`, `HandleTags`.

Repositories allows you to modify the default behavior of your models by providing some entry points in the form of methods that you might implement:

- for filtering:

```php
<?php

// implement the filter method
public function filter($query, array $scopes = []) {

    // and use the following helpers

    // add a where like clause
    $this->addLikeFilterScope($query, $scopes, 'field_in_scope');

    // add orWhereHas clauses
    $this->searchIn($query, $scopes, 'field_in_scope', ['field1', 'field2', 'field3']);

    // add a whereHas clause
    $this->addRelationFilterScope($query, $scopes, 'field_in_scope', 'relationName');

    // or just go manually with the $query object
    if (isset($scopes['field_in_scope'])) {
      $query->orWhereHas('relationName', function ($query) use ($scopes) {
          $query->where('field', 'like', '%' . $scopes['field_in_scope'] . '%');
      });
    }

    // don't forget to call the parent filter function
    return parent::filter($query, $scopes);
}
```

- for custom ordering:

```php
<?php

// implement the order method
public function order($query, array $orders = []) {
    // don't forget to call the parent order function
    return parent::order($query, $orders);
}
```

- for custom form fieds

```php
<?php

// implement the getFormFields method
public function getFormFields($object) {
    // don't forget to call the parent getFormFields function
    $fields = parent::getFormFields($object);

    // get fields for a browser
    $fields['browsers']['relationName'] = $this->getFormFieldsForBrowser($object, 'relationName');

    // get fields for a repeater
    $fields = $this->getFormFieldsForRepeater($object, 'relationName');

    // return fields
    return $fields
}

```

- for custom field preparation before create action


```php
<?php

// implement the prepareFieldsBeforeCreate method
public function prepareFieldsBeforeCreate($fields) {
    // don't forget to call the parent prepareFieldsBeforeCreate function
    return parent::prepareFieldsBeforeCreate($fields);
}

```

- for custom field preparation before save action


```php
<?php

// implement the prepareFieldsBeforeSave method
public function prepareFieldsBeforeSave($object, $fields) {
    // don't forget to call the parent prepareFieldsBeforeSave function
    return parent:: prepareFieldsBeforeSave($object, $fields);
}

```

- for after save actions (like attaching a relationship)

```php
<?php

// implement the afterSave method
public function afterSave($object, $fields) {
    // for exemple, to sync a many to many relationship
    $this->updateMultiSelect($object, $fields, 'relationName');
    
    // which will simply run the following for you
    $object->relationName()->sync($fields['relationName'] ?? []);
    
    // or, to save a oneToMany relationship
    $this->updateOneToMany($object, $fields, 'relationName', 'formFieldName', 'relationAttribute')
    
    // or, to save a belongToMany relationship used with the browser field
    $this->updateBrowser($object, $fields, 'relationName');
    
    // or, to save a hasMany relationship used with the repeater field
    $this->updateRepeater($object, $fields, 'relationName');
    
    // or, to save a belongToMany relationship used with the repeater field
    $this->updateRepeaterMany($object, $fields, 'relationName', false);
    
    parent::afterSave($object, $fields);
}

```

- for hydrating the model for preview of revisions

```php
<?php

// implement the hydrate method
public function hydrate($object, $fields)
{
    // for exemple, to hydrate a belongToMany relationship used with the browser field
    $this->hydrateBrowser($object, $fields, 'relationName');

    // or a multiselect
    $this->hydrateMultiSelect($object, $fields, 'relationName');

    // or a repeater
    $this->hydrateRepeater($object, $fields, 'relationName');

    return parent::hydrate($object, $fields);
}
```

#### Form fields

Wrap them into the following in your module `form` view (`resources/views/admin/moduleName/form.blade.php`):

```php
@extends('twill::layouts.form')
@section('contentFields')
    @formField('...', [...])
    ...
@stop
```

The idea of the `contentFields` section is to contain the most important fields and the block editor as the last field.

If you have attributes, relationships, extra images, file attachments or repeaters, you'll want to add a `fieldsets` section after the `contentFields` section and use the `a17-fieldset` Vue component to create new ones like in the following example:

```php
@extends('twill::layouts.form', [
    'additionalFieldsets' => [
        ['fieldset' => 'attributes', 'label' => 'Attributes'],
    ]
])

@section('contentFields')
    @formField('...', [...])
    ...
@stop

@section('fieldsets')
    <a17-fieldset title="Attributes" id="attributes">
        @formField('...', [...])
        ...
    </a17-fieldset>
@stop
```

The additional fieldsets array passed to the form layout will display a sticky navigation of your fieldset on scroll.
You can also rename the content section by passing a `contentFieldsetLabel` property to the layout.

##### Input
>![screenshot](_media/input.png)

```php
@formField('input', [
    'name' => 'subtitle',
    'label' => 'Subtitle',
    'maxlength' => 100,
    'required' => true,
    'note' => 'Hint message goes here',
    'placeholder' => 'Placeholder goes here',
])

@formField('input', [
    'translated' => true,
    'name' => 'subtitle_translated',
    'label' => 'Subtitle (translated)',
    'maxlength' => 250,
    'required' => true,
    'note' => 'Hint message goes here',
    'placeholder' => 'Placeholder goes here',
    'type' => 'textarea',
    'rows' => 3
])
```

##### WYSIWYG
>![screenshot](_media/wysiwyg.png)

```php
@formField('wysiwyg', [
    'name' => 'case_study',
    'label' => 'Case study text',
    'toolbarOptions' => ['list-ordered', 'list-unordered'],
    'placeholder' => 'Case study text',
    'maxlength' => 200,
    'note' => 'Hint message',
])

@formField('wysiwyg', [
    'name' => 'case_study',
    'label' => 'Case study text',
    'toolbarOptions' => [ [ 'header' => [1, 2, false] ], 'list-ordered', 'list-unordered', [ 'indent' => '-1'], [ 'indent' => '+1' ] ],
    'placeholder' => 'Case study text',
    'maxlength' => 200,
    'editSource' => true,
    'note' => 'Hint message',
])
```

##### Medias
>![screenshot](_media/medias.png)

```php
@formField('medias', [
    'name' => 'cover',
    'label' => 'Cover image',
    'note' => 'Minimum image width 1300px'
])

@formField('medias', [
    'name' => 'slideshow',
    'label' => 'Slideshow',
    'max' => 5,
    'note' => 'Minimum image width: 1500px'
])
```

##### Datepicker
>![screenshot](_media/datepicker.png)

```php
@formField('date_picker', [
    'name' => 'event_date',
    'label' => 'Event date',
    'minDate' => '2017-09-10 12:00',
    'maxDate' => '2017-12-10 12:00'
])
```

##### Select
>![screenshot](_media/select.png)

```php
@formField('select', [
    'name' => 'office',
    'label' => 'Office',
    'placeholder' => 'Select an office',
    'options' => [
        [
            'value' => 1,
            'label' => 'New York'
        ],
        [
            'value' => 2,
            'label' => 'London'
        ],
        [
            'value' => 3,
            'label' => 'Berlin'
        ]
    ]
])
```

##### Select unpacked
>![screenshot](_media/selectunpacked.png)

```php
@formField('select', [
    'name' => 'discipline',
    'label' => 'Discipline',
    'unpack' => true,
    'options' => [
        [
            'value' => 'arts',
            'label' => 'Arts & Culture'
        ],
        [
            'value' => 'finance',
            'label' => 'Banking & Finance'
        ],
        [
            'value' => 'civic',
            'label' => 'Civic & Public'
        ],
        [
            'value' => 'design',
            'label' => 'Design & Architecture'
        ],
        [
            'value' => 'education',
            'label' => 'Education'
        ],
        [
            'value' => 'entertainment',
            'label' => 'Entertainment'
        ],
    ]
])
```

##### Multi select
>![screenshot](_media/multiselect.png)

```php
@formField('multi_select', [
    'name' => 'sectors',
    'label' => 'Sectors',
    'options' => [
        [
            'value' => 'arts',
            'label' => 'Arts & Culture'
        ],
        [
            'value' => 'finance',
            'label' => 'Banking & Finance'
        ],
        [
            'value' => 'civic',
            'label' => 'Civic & Public'
        ],
        [
            'value' => 'design',
            'label' => 'Design & Architecture'
        ],
        [
            'value' => 'education',
            'label' => 'Education'
        ]
    ]
])

@formField('multi_select', [
    'name' => 'sectors_bis',
    'label' => 'Sectors bis',
    'min' => 1,
    'max' => 2,
    'options' => [
        [
            'value' => 'arts',
            'label' => 'Arts & Culture'
        ],
        [
            'value' => 'finance',
            'label' => 'Banking & Finance'
        ],
        [
            'value' => 'civic',
            'label' => 'Civic & Public'
        ],
        [
            'value' => 'design',
            'label' => 'Design & Architecture'
        ],
        [
            'value' => 'education',
            'label' => 'Education'
        ],
        [
            'value' => 'entertainment',
            'label' => 'Entertainment'
        ],
    ]
])
```

##### Block editor
>![screenshot](_media/blockeditor.png)

```php
@formField('block_editor', [
    'blocks' => ['title', 'quote', 'text', 'image', 'grid', 'test', 'publications', 'news']
])
```

##### Repeater
>![screenshot](_media/repeater.png)

```php
<a17-fieldset title="Videos" id="videos" :open="true">
    @formField('repeater', ['type' => 'video'])
</a17-fieldset>
```

##### Browser
>![screenshot](_media/browser.png)

```php
<a17-fieldset title="Related" id="related" :open="true">
    @formField('browser', [
        'label' => 'Publications',
        'max' => 4,
        'name' => 'publications',
        'endpoint' => 'http://admin.cms-sandbox.dev.a17.io/content/posts/browser'
    ])
</a17-fieldset>
```

##### Files
>![screenshot](_media/files.png)

```php
@formField('files', [
    'name' => 'single_file',
    'label' => 'Single file',
    'note' => 'Add one file (per language)'
])

@formField('files', [
    'name' => 'single_file_no_translate',
    'label' => 'Single file (no translate)',
    'note' => 'Add one file',
    'noTranslate' => true,
])

@formField('files', [
    'name' => 'files',
    'label' => 'Files',
    'noTranslate' => true,
    'max' => 4,
])
```

##### Map
>![screenshot](_media/map.png)

```php
@formField('map', [
    'name' => 'location',
    'label' => 'Location',
    'showMap' => false,
])
```

##### Color
>![screenshot](_media/color.png)

```php
@formField('color', [
    'name' => 'main-color',
    'label' => 'Main color'
])
```

##### Single checkbox

```php
@formField('checkbox', [
    'name' => 'featured',
    'label' => 'Featured'
])
```

##### Multiple checkboxes (multi select as checkboxes)

```php
@formField('checkboxes', [
    'name' => 'sectors',
    'label' => 'Sectors',
    'note' => '3 sectors max & at least 1 sector',
    'min' => 1,
    'max' => 3,
    'inline' => true/false
    'options' => [
        [
            'value' => 'arts',
            'label' => 'Arts & Culture'
        ],
        [
            'value' => 'finance',
            'label' => 'Banking & Finance'
        ],
        [
            'value' => 'civic',
            'label' => 'Civic & Public'
        ],
    ]
])
```

##### Radios

```php
@formField('radios', [
    'name' => 'discipline',
    'label' => 'Discipline',
    'default' => 'civic',
    'inline' => true/false,
    'options' => [
        [
            'value' => 'arts',
            'label' => 'Arts & Culture'
        ],
        [
            'value' => 'finance',
            'label' => 'Banking & Finance'
        ],
        [
            'value' => 'civic',
            'label' => 'Civic & Public'
        ],
    ]
])
```

### Block editor

#### How to add blocks to the CMS
The block editor form field lets you add content freely to your module. The blocks can be easy added and rearranged.
Once a block is created, it can be used/added to any module by adding the corresponding traits.

In order to add a block editor you need to add the `block_editor` field to your module form. e.g.:

```php
@extends('twill::layouts.form')

@section('contentFields')
    @formField('input', [
        'name' => 'description',
        'label' => 'Description',
    ])
...
    @formField('block_editor')
@stop
```

By adding the `@formField('block_editor')` you've enabled all the available blocks. To scope the *blocks* that will be displayed you can add a second parameter with the *blocks* key. e.g.:

```php
@formField('block_editor', [
    'blocks' => ['quote', 'image']
])
```

The *blocks* that can be added need to be defined under the `views/admin/blocks` folder.
The blocks can be defined exactly like a regular form. e.g.:

filename: ```admin/blocks/quote.blade.php```
```php
@formField('input', [
    'name' => 'quote',
    'type' => 'textarea',
    'label' => 'Quote text',
    'maxlength' => 250,
    'rows' => 4
])
```

Once the form is created an _artisan_ task needs to be run to generate the _Vue_ component for this block.

`php artisan twill:blocks`

Example output:
```shell
$ php artisan twill:blocks
Starting to scan block views directory...
Block Quote generated successfully
All blocks have been generated!
$
```

The task will generate a file inside the folder `resources/assets/js/blocks/`. Do not ignore those files in Git.

filename: ```resources/assets/js/blocks/BlockQuote.vue```

```js
<template>
    <div class="block__body">
        <a17-textfield label="Quote text" :name="fieldName('quote')" type="textarea" :maxlength="250" :rows="4" in-store="value" ></a17-textfield>
    </div>
</template>

<script>
  import BlockMixin from '@/mixins/block'

  export default {
    mixins: [BlockMixin]
  }
</script>

```

With that the *block* is ready to be used on the form, it just needs to be enabled in the CMS configuration.
For it a `block_editor` key is required and inside you can define the list of `blocks` available in your project.

filename: ```config/twill.php```

```php
    'block_editor' => [
        'blocks' => [
            ...
            'quote' => [
                'title' => 'Quote',
                'icon' => 'text',
                'component' => 'a17-block-quote',
            ],
            ..
        ]
    ]
```

Please note the naming convention. If the *block* added is _quote_ then the component should be prefixed with _a17-block-_.
If you added a block like *my_awesome_block* then you need to make sure that keep the same name as _key_ and the _component name_ with the prefix. e.g.:
```php
    'block_editor' => [
        'blocks' => [
            ...
            'my_awesome_block' => [
                'title' => 'Title for my awesome block',
                'icon' => 'text',
                'component' => 'a17-block-my_awesome_block',
            ],
            ..
        ]
```


After having the blocks added and the configuration set it is required to have the traits added inside your module(Laravel Model).
Add the corresponding traits to your model and repository, respectively `HasBlocks` and `HandleBlocks`.

filename: ```app/Models/Article.php```
```php
<?php

namespace App\Models;

use A17\Twill\Models\Behaviors\HasBlocks;
use A17\Twill\Models\Model;

class Article extends Model
{
    use HasBlocks;

    ...
}
```

filename: ```app/Repositories/ArticleRepository.php```
```php
<?php

namespace App\Repositories;

use A17\Twill\Repositories\Behaviors\HandleBlocks;
use A17\Twill\Repositories\ModuleRepository;
use App\Models\Article;

class ArticleRepository extends ModuleRepository
{
    use HandleBlocks;

    ...
}
```

##### Common Errors
- Make sure your project have the blocks table migration. If not, you can find the `create_blocks_table` migration in the toolkit's source in `migrations`.

- Not running the _twill:blocks_ task.

- Not adding the *block* to the configuration.

- Not using the same name of the block inside the configuration.

#### How to add Repeater blocks
Lets say that it is requested to have an Accordion on Articles, where each item should have a _Header_ and a _Description_.
This accordion can be moved around along with the rest of the blocks.
On the Article (module) form we have:

filename: ```views/admin/articles/form.blade.php```
```php
@extends('twill::layouts.form')

@section('contentFields')
    @formField('input', [
        'name' => 'description',
        'label' => 'Description',
    ])
...
    @formField('block_editor')
@stop

```

- Inside the *container block* file, add a repeater form field:

  filename: ```admin/blocks/accordion.blade.php```
```php
  @formField('repeater', ['type' => 'accordion_item'])
```


- Add it on the config/twill.php
```php
    'block_editor' => [
        'blocks' => [
            ...
            'accordion' => [
                'title' => 'Accordion',
                'icon' => 'text',
                'component' => 'a17-block-accordion',
            ],
            ..
        ]
    ]
```

- Add the *item block*, the one that will be reapeated inside the *container block*
  filename: ```admin/blocks/accordion_item.blade.php```

```php
  @formField('input', [
      'name' => 'header',
      'label' => 'Header'
  ])

  @formField('input', [
      'type' => 'textarea',
      'name' => 'description',
      'label' => 'Description',
      'rows' => 4
  ])
```

- Add it on the config/twill.php on the repeaters section

```php
    'block_editor' => [
        'blocks' => [
            ...
            'accordion' => [
                'title' => 'Accordion',
                'icon' => 'text',
                'component' => 'a17-block-accordion',
            ],
            ..
        ],
        'repeaters' => [
            ...
            'accordion_item' => [
                'title' => 'Accordion',
                'trigger' => 'Add accordion',
                'component' => 'a17-block-accordion_item',
                'max' => 10,
            ],
            ...
        ]
    ]
```

##### Common errors:
- If you add the *container block* to the _repeaters_ section inside the config, it won't work, e.g.:
```php
        'repeaters' => [
            ...
            'accordion' => [
                'title' => 'Accordion',
                'trigger' => 'Add accordion',
                'component' => 'a17-block-accordion',
                'max' => 10,
            ],
            ...
        ]
```

- If you use a different name for the block inside the _repeaters_ section, neither. e. g.:
```php
        'repeaters' => [
            ...
            'accordion-item' => [
                'title' => 'Accordion',
                'trigger' => 'Add accordion',
                'component' => 'a17-block-accordion_item',
                'max' => 10,
            ],
            ...
        ]
```

- Not adding the *item block* to the _repeaters_ section.

#### How to add Browser Fields
If you are requested to enable the possibility to add a related model, then the browser fields are the match.
If you have an Article that can have related products.

On the Article(entity) form we have:

filename: ```views/admin/articles/form.blade.php```
```php
@extends('twill::layouts.form')

@section('contentFields')
    @formField('input', [
        'name' => 'description',
        'label' => 'Description',
    ])
...
    @formField('block_editor')
@stop

```

- Add the block editors that will handle the `Browser Field`
filename: ```views/admin/blocks/products.blade.php```
```php
    @formField('browser', [
        'routePrefix' => 'content',
        'moduleName' => 'products',
        'name' => 'products',
        'label' => 'Products',
        'max' => 10
    ])
```

- Define the block in the configuration like any other block in the config/twill.php.
```php
    'blocks' => [
        ...
        'products' => [
            'title' => 'Products',
            'icon' => 'text',
            'component' => 'a17-block-products',
        ],
```

- After that, it is required to add the Route Prefixes. e.g.:
```php
    'block_editor' => [
        'blocks' => [
            ...
            'product' => [
                'title' => 'Product',
                'icon' => 'text',
                'component' => 'a17-block-products',
            ],
            ...
        ],
        'repeaters' => [
                ...
        ],
        'browser_route_prefixes' => [
            'products' => 'content',
        ],
    ]
```

#### Rendering blocks
As long as you have access to a model instance that uses the HasBlocks trait in a view, you can call the `renderBlocks` helper on it to render the list of blocks that were created from the CMS. By default, this function will loop over all the blocks and their child blocks and render a Blade view located in `resources/views/site/blocks` with the same name as the block key you specified in your Twill configuration and module form. 

In the frontend templates, you can call the `renderBlocks` helper like this:

```php
{!! $item->renderBlocks() !!}
```

If you want to render child blocks (when using repeaters) inside the parent block, you can do the following:

```php
{!! $work->renderBlocks(false) !!}
```

If you need to swap out a block view for a specific module (let’s say you used the same block in 2 modules of the CMS but need different rendering), you can do the following:

```php
{!! $work->renderBlocks(true, [
  'block-type' => 'view.path',
  'block-type-2' => 'another.view.path'
]) !!}
```

In those Blade view, you will have access to a `$block`variable with a couple of helper function available to retrieve the block content:

```php
{{ $block->input('inputNameYouSpecifiedInTheBlockFormField') }}
{{ $block->translatedinput('inputNameYouSpecifiedInATranslatedBlockFormField') }}
```

If the block has a media field, you can refer to the Media Library documentation below to learn about the `HasMedias` trait helpers.
To give an exemple:

```php
{{ $block->image('mediaFieldName', 'cropNameFromBlocksConfig') }}
{{ $block->images('mediaFieldName', 'cropNameFromBlocksConfig')}}
```

### Media Library
>![screenshot](_media/medialibrary.png)

#### Storage provider
The media and files libraries currently support S3 and local storage. Head over to the `twill` configuration file to setup your storage disk and configurations. Also check out the S3 direct upload section of this documentation to setup your IAM users and bucket if you want to use S3 as a storage provider.

#### Image Rendering Service
This package currently ship with only one rendering service, [Imgix](https://www.imgix.com/). It is very simple to implement another one like [Cloudinary](http://cloudinary.com/) or even a local service like [Glide](http://glide.thephpleague.com/) or [Croppa](https://github.com/BKWLD/croppa).
You would have to implement the `ImageServiceInterface` and modify your `twill` configuration value `media_library.image_service` with your implementation class.
Here are the methods you would have to implement:

```php
<?php

public function getUrl($id, array $params = []);
public function getUrlWithCrop($id, array $crop_params, array $params = []);
public function getUrlWithFocalCrop($id, array $cropParams, $width, $height, array $params = []);
public function getLQIPUrl($id, array $params = []);
public function getSocialUrl($id, array $params = []);
public function getCmsUrl($id, array $params = []);
public function getRawUrl($id);
public function getDimensions($id);
public function getSocialFallbackUrl();
public function getTransparentFallbackUrl();
```

$crop_params will be an array with the following keys: crop_x, crop_y, crop_w and crop_y. If the service you are implementing doesn't support focal point cropping, you can call the getUrlWithCrop from your implementation.

#### Role & Crop params
Each of the data models in your application can have different images roles and crop.

For exemple, roles for a People model could be `profile` and `cover`. This allow you display different images for your data modal in the design, depending on the current screen.

Crops are complementary or can be used on their own with a single role to define multiple cropping ratios on the same image.

For example, your Person `cover` image could have a `square` crop for mobile screen, but could use a `16/9` crop on larger screen. Those values are editable at your convenience for each model, even if there are already some crop created in the CMS.

The only thing you have to do to make it work is to compose your model and repository with the appropriate traits, respectively `HasMedias` and `HandleMedias`, setup your `$mediaParams` configuration and use the `medias` form partial in your form view (more info in the CRUD section).

When it comes to using those data model images in the frontend site, there are a few methods on the `HasMedias` trait that will help you to retrieve them for each of your layouts:

```php
<?php

/**
 * Returns the url of the associated image for $roleName and $cropName.
 * Optionally add params compatible with the current image service in use like w or h.
 * Optionally indicate that you can provide a fallback so that this method will return null
 * instead of the fallback image.
 * Optionally indicate that you are displaying this image in the CMS views.
 * Optionally provide a $media object if you already retrieved one to prevent more SQL requests.
 */
$model->image($roleName, $cropName[, array $params, $has_fallback, $cms, $media])

/**
 * Returns an array of images URLs assiociated with $roleName and $cropName with appended $params.
 * Use this in conjunction with a media form field with the with_multiple and max option.
 */
$model->images($roleName, $cropName[, array $params])

/**
 * Returns the image for $roleName and $cropName with default social image params and $params appended
 */
$model->socialImage($roleName, $cropName[, array $params, $has_fallback])

/**
 * Returns the lqip base64 encoded string from the database for $roleName and $cropName.
 * Use this in conjunction with the RefreshLQIP Artisan command.
 */
$model->lowQualityImagePlaceholder($roleName, $cropName[, array $params, $has_fallback])

/**
 * Returns the image for $roleName and $cropName with default CMS image params and $params appended.
 */
$model->cmsImage($roleName, $cropName[, array $params, $has_fallback])

/**
 * Returns the alt text of the image associated with $roleName.
 */
$model->imageAltText($roleName)

/**
 * Returns the caption of the image associated with $roleName.
 */
$model->imageCaption($roleName)

/**
 * Returns the image object associated with $roleName.
 */
$model->imageObject($roleName)
```

### File library
The file library is much simpler but also work with S3 and local storage. To associate files to your model, use the `HasFiles` and `HandleFiles` traits, the `$filesParams` configuration and the `files` form partial.

When it comes to using those data model files in the frontend site, there are a few methods on the `HasFiles` trait that will help you to retrieve direct URLs:

```php
<?php

/**
 * Returns the url of the associated file for $roleName.
 * Optionally indicate which locale of the file if your site has multiple languages.
 * Optionally provide a $file object if you already retrieved one to prevent more SQL requests.
 */
$model->file($roleName[, $locale, $file])

/**
 * Returns an array of files URLs assiociated with $roleName.
 * Use this in conjunction with a files form field with the with_multiple and max option.
 */
$model->filesList($roleName[, $locale])

/**
 * Returns the file object associated with $roleName.
 */
$model->fileObject($roleName)
```

### S3 direct upload

Create a IAM user for full access to the bucket and use its credentials in your `.env` file. You can use the following IAM permission:

```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Action": "s3:*",
            "Resource": [
                "arn:aws:s3:::YOUR_BUCKER_IDENTIFIER/*",
                "arn:aws:s3:::YOUR_BUCKER_IDENTIFIER"
            ]
        }
    ]
}
```

Create a IAM user for Imgix (or any other similar service) with only read-only access to your bucket and use its credentials to create an S3 source. You can use the following IAM permission:

```json
{
    "Statement": [
        {
            "Effect": "Allow",
            "Action": [
                "s3:GetObject",
                "s3:ListBucket",
                "s3:GetBucketLocation"
            ],
            "Resource": [
                "arn:aws:s3:::YOUR_BUCKER_IDENTIFIER/*",
                "arn:aws:s3:::YOUR_BUCKER_IDENTIFIER"
            ]
        }
    ]
}
```

For improved security, modify the bucket CORS configuration to accept uploads request from your admin domain only:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<CORSConfiguration xmlns="http://s3.amazonaws.com/doc/2006-03-01/">
    <CORSRule>
        <AllowedOrigin>http(s)://YOUR_ADMIN_DOMAIN</AllowedOrigin>
        <AllowedMethod>POST</AllowedMethod>
        <AllowedMethod>PUT</AllowedMethod>
        <AllowedMethod>DELETE</AllowedMethod>
        <MaxAgeSeconds>3000</MaxAgeSeconds>
        <ExposeHeader>ETag</ExposeHeader>
        <AllowedHeader>*</AllowedHeader>
    </CORSRule>
</CORSConfiguration>
```

### Users management

Authentication and authorization are provided by default in Laravel. This package simply leverages it and configure the views with the A17 CMS UI Toolkit for you. By default, users can login at `/login` and also reset their password through that screen. New users have to start by resetting their password before initial access to the admin application. You should redirect users to anywhere you want in your application after they login. The twill configuration file has an option for you to change the default redirect path (`auth_login_redirect_path`).

#### Roles
The package currently only provides 3 different roles:
- view only
- publisher
- admin

#### Permissions
View only users are able to:
- login
- view CRUD listings
- filter CRUD listings
- view media/file library
- download original files from the media/file library
- edit their own profile

Publishers have the same permissions as view only users plus:
- full CRUD permissions
- publish
- sort
- upload new images/files to the media/file library

Admin user have the same permissions as publisher users plus:
- full permissions on users

There is also a super admin user that can impersonate other users at `/users/impersonate/{id}`. This can be really useful for you to test your features with different user roles without having to logout/login manually. Also when debugging a ticket reported by a specific user. Stop impersonating by going to `/users/impersonate/stop`.


#### Extending user roles and permissions
You can create new permissions on the existing roles by using the Gate façade in your `AuthServiceProvider`. The new can middleware Laravel provides by default is very easy to use, either through route definition or controller constructor.

You should follow the Laravel documentation regarding [authorization](https://laravel.com/docs/5.3/authorization). It's pretty good. Also if you would like to bring administration of roles and permissions to the admin application, [spatie/laravel-permission](https://github.com/spatie/laravel-permission) would probably be your best friend. The Opera CMS had that feature but it was not very well developed which makes it a pain to use.


### Buckets

Buckets allow you to provide admins with featured content management screens. You can add multiple pages of buckets anywhere you'd like in your CMS navigation and, in each page, multiple buckets with differents rules and accepted modules. In the following example, we will assume that our application has a Guide model and that we want to feature guides on the homepage of our site. Our site's homepage has multiple zones for featured guides: a primary zone, that shows only one featured guide, and a secondary zone, that shows guides in a carousel of maximum 10 items.

First, you will need to enable the buckets feature. In `config/twill.php`:
```php
'enabled' => [
    'buckets' => true,
],
```

Then, define your buckets configuration:

```php
'buckets' => [
    'homepage' => [
        'name' => 'Home',
        'buckets' => [
            'home_primary_feature' => [
                'name' => 'Home primary feature',
                'bucketables' => [
                    [
                        'module' => 'guides',
                        'name' => 'Guides',
                        'scopes' => ['published' => true],
                    ],
                ],
                'max_items' => 1,
            ],
            'home_secondary_features' => [
                'name' => 'Home secondary features',
                'bucketables' => [
                    [
                        'module' => 'guides',
                        'name' => 'Guides',
                        'scopes' => ['published' => true],
                    ],
                ],
                'max_items' => 10,
            ],
        ],
    ],
],
```

You can allow mixing modules in a single bucket by adding more modules to the `bucketables` array.
Each `bucketable`should have its [model morph map](https://laravel.com/docs/5.5/eloquent-relationships#polymorphic-relations) defined because features are stored in a polymorphic table.
In your AppServiceProvider, you can do it like the following:

```php
use Illuminate\Database\Eloquent\Relations\Relation;
...
public function boot()
{
    Relation::morphMap([
        'guides' => 'App\Models\Guide',
    ]);
}
```

Finally, add a link to your buckets page in your CMS navigation:

```php
return [
   'featured' => [
       'title' => 'Features',
       'route' => 'admin.featured.homepage',
       'primary_navigation' => [
           'homepage' => [
               'title' => 'Homepage',
               'route' => 'admin.featured.homepage',
           ],
       ],
   ],
   ...
];
```

By default, the buckets page (in our example, only homepage) will live at under the /featured prefix.
But you might need to split your buckets page between sections of your CMS. For example if you want to have the homepage bucket page of our example under the /pages prefix in your navigation, you can use another configuration property:

```php
'bucketsRoutes' => [
    'homepage' => 'pages'
]
```

### Automated settings pages
TODO

### Frontend previews setup
TODO

### Using as a headless platform
TODO

### Extending Vue components
TODO

### Other useful packages

- [laravel/scout](https://laravel.com/docs/5.3/scout) provide full text search on your Eloquent models.
- [laravel/passport](https://laravel.com/docs/5.3/passport) makes API authentication a breeze.
- [spatie/laravel-fractal](https://github.com/spatie/laravel-fractal) is a nice and easy integration with [Fractal](http://fractal.thephpleague.com) to create APIs.
- [laravel/socialite](https://github.com/laravel/socialite) provides an expressive, fluent interface to OAuth authentication.
- [spatie/laravel-responsecache](https://github.com/spatie/laravel-responsecache) can speed up your app by caching the entire response.
- [spatie/laravel-backup](https://github.com/spatie/laravel-backup) creates a backup of your application. The backup is a zipfile that contains all files in the directories you specify along with a dump of your database.
- [jenssegers/rollbar](https://github.com/jenssegers/laravel-rollbar) adds a listener to Laravel's logging component to work with Rollbar.
- [sentry/sentry-laravel](https://github.com/getsentry/sentry-laravel) is a Laravel integration for Sentry.
- [arcanedev/log-viewer](https://github.com/ARCANEDEV/LogViewer) allows you to manage and keep track of each one of your logs files in a nice web UI.
- [roumen/sitemap](https://github.com/RoumenDamianoff/laravel-sitemap) is a very complelete sitemap generator.
- [flynsarmy/csv-seeder](https://github.com/Flynsarmy/laravel-csv-seeder) allows CSV based database seeds.
- [ufirst/lang-import-export](https://github.com/ufirstgroup/laravel-lang-import-export)  provides artisan commands to import and export language files from and to CSV
- [nikaia/translation-sheet](https://github.com/nikaia/translation-sheet) allows translating Laravel languages files using a Google Spreadsheet.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Upgrade guide

### Upgrading to 1.0 from 0.7

##### Assets
Remove the `public/assets/admin` directory content from your project.
Refer to the frontend section of the setup documentation to update your project's CMS assets management.

##### Module index view
Delete your module admin index view blade file and move your columns definition to the corresponding module controller `$indexColumns` property.

Refer to the CRUD modules Controller documentation to learn about other index options.

By default, the index is ordered by and search in the `title` column. Use the `$titleColumnKey`, `$defaultFilters` and `$defaultOrders` controller properties to customize it.

##### Module form view
Only keep `@formField` instructions, remove everything else and wrap them into the following:

```php
@extends('twill::layouts.form')
@section('contentFields')
    @formField('input', [...])
    ...
@stop
```

Refer to the CRUD modules form fields documentation above to learn about the new available form fields.

Globally, `field_name` have been renamed `label` and `field` have been renamed `name`.

In select fields, `list` have been renamed `options`. The easiest way to feed for a module a select is by using the `listAll` repository method and return it from your module controller `formData` function.

##### CMS navigation
If you have an entry for CMS users, you can now remove it as it is automatically added to the top right user dropdown.
