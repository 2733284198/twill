<?php

namespace A17\CmsToolkit\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;
use Illuminate\Support\Str;

class ModuleMake extends Command
{
    protected $signature = 'cms-toolkit:module {moduleName}
        {--T|hasTranslation}
        {--S|hasSlug}
        {--M|hasMedias}
        {--F|hasFiles}
        {--P|hasPosition}';

    protected $description = 'Create a new CMS Module';

    protected $files;

    protected $composer;

    protected $modelTraits;

    protected $repositoryTraits;

    public function __construct(Filesystem $files, Composer $composer)
    {
        parent::__construct();

        $this->files = $files;
        $this->composer = $composer;

        $this->modelTraits = ['HasTranslation', 'HasSlug', 'HasMedias', 'HasFiles', 'HasPosition'];
        $this->repositoryTraits = ['HandleTranslations', 'HandleSlugs', 'HandleMedias', 'HandleFiles'];
    }

    public function fire()
    {
        $moduleName = $this->argument('moduleName');

        $translatable = $this->option('hasTranslation') ?? false;
        $sluggable = $this->option('hasSlug') ?? false;
        $mediable = $this->option('hasMedias') ?? false;
        $fileable = $this->option('hasFiles') ?? false;
        $sortable = $this->option('hasPosition') ?? false;

        $activeTraits = [$translatable, $sluggable, $mediable, $fileable, $sortable];

        $modelName = Str::studly(Str::singular($moduleName));

        $this->createMigration($moduleName);
        $this->createModels($modelName, $translatable, $sluggable, $sortable, $activeTraits);
        $this->createRepository($modelName, $activeTraits);
        $this->createController($moduleName, $modelName);
        $this->createRequest($modelName);
        $this->createViews($moduleName, $translatable);

        $this->info("\nStart by filling in the migration and models.");
        $this->info("Add Route::module('{$moduleName}'); to your admin routes file.");
        $this->info("Setup a new CMS menu item in config/cms-navigation.php.");
        $this->info("Setup your index and form views.");
        $this->info("Enjoy.");

        $this->composer->dumpAutoloads();
    }

    private function createMigration($moduleName = 'items')
    {
        $table = Str::snake($moduleName);

        $tableClassName = Str::studly($table);

        $className = "Create{$tableClassName}Tables";

        if (!class_exists($className)) {
            $migrationName = 'create_' . $table . '_tables';

            $migrationPath = $this->laravel->databasePath() . '/migrations';

            $fullPath = $this->laravel['migration.creator']->create($migrationName, $migrationPath);

            $stub = str_replace(
                ['{{table}}', '{{translationTable}}', '{{tableClassName}}'], [$table, Str::singular($table), $tableClassName], $this->files->get(__DIR__ . '/stubs/migration.stub')
            );

            $this->files->put($fullPath, $stub);

            $this->info('Migration created successfully! Add some fields!');
        }
    }

    private function createModels($modelName = 'Item', $translatable = false, $sluggable = false, $sortable = false, $activeTraits = [])
    {
        if (!$this->files->isDirectory(app_path('Models'))) {
            $this->files->makeDirectory(app_path('Models'));
        }

        if ($translatable) {
            if (!$this->files->isDirectory(app_path('Models/Translations'))) {
                $this->files->makeDirectory(app_path('Models/Translations'));
            }

            $modelTranslationClassName = $modelName . 'Translation';

            $stub = str_replace('{{modelTranslationClassName}}', $modelTranslationClassName, $this->files->get(__DIR__ . '/stubs/model_translation.stub'));

            $this->files->put(app_path('Models/Translations/' . $modelTranslationClassName . '.php'), $stub);
        }

        if ($sluggable) {
            if (!$this->files->isDirectory(app_path('Models/Slugs'))) {
                $this->files->makeDirectory(app_path('Models/Slugs'));
            }

            $modelSlugClassName = $modelName . 'Slug';

            $stub = str_replace(['{{modelSlugClassName}}', '{{modelName}}'], [$modelSlugClassName, Str::snake($modelName)], $this->files->get(__DIR__ . '/stubs/model_slug.stub'));

            $this->files->put(app_path('Models/Slugs/' . $modelSlugClassName . '.php'), $stub);
        }

        $modelClassName = $modelName;

        $activeModelTraits = [];

        foreach ($activeTraits as $index => $traitIsActive) {
            if ($traitIsActive) {
                !isset($this->modelTraits[$index]) ?: $activeModelTraits[] = $this->modelTraits[$index];
            }
        }

        $activeModelTraitsString = empty($activeModelTraits) ? '' : 'use ' . rtrim(implode(', ', $activeModelTraits), ', ') . ';';

        $activeModelTraitsImports = empty($activeModelTraits) ? '' : "use A17\CmsToolkit\Models\Behaviors\\" . implode(";\nuse A17\CmsToolkit\Models\Behaviors\\", $activeModelTraits) . ";";

        $activeModelImplements = $sortable ? 'implements Sortable' : '';

        if ($sortable) {
            $activeModelTraitsImports .= "\nuse A17\CmsToolkit\Models\Behaviors\Sortable;";
        }

        $stub = str_replace(['{{modelClassName}}', '{{modelTraits}}', '{{modelImports}}', '{{modelImplements}}'], [$modelClassName, $activeModelTraitsString, $activeModelTraitsImports, $activeModelImplements], $this->files->get(__DIR__ . '/stubs/model.stub'));

        $this->files->put(app_path('Models/' . $modelClassName . '.php'), $stub);

        $this->info('Models created successfully! Fill your fillables!');
    }

    private function createRepository($modelName = 'Item', $activeTraits = [])
    {
        if (!$this->files->isDirectory(app_path('Repositories'))) {
            $this->files->makeDirectory(app_path('Repositories'));
        }

        $repositoryClassName = $modelName . 'Repository';

        $activeRepositoryTraits = [];

        foreach ($activeTraits as $index => $traitIsActive) {
            if ($traitIsActive) {
                !isset($this->repositoryTraits[$index]) ?: $activeRepositoryTraits[] = $this->repositoryTraits[$index];
            }
        }

        $activeRepositoryTraitsString = empty($activeRepositoryTraits) ? '' : 'use ' . (empty($activeRepositoryTraits) ? "" : rtrim(implode(', ', $activeRepositoryTraits), ', ') . ';');

        $activeRepositoryTraitsImports = empty($activeRepositoryTraits) ? '' : "use A17\CmsToolkit\Repositories\Behaviors\\" . implode(";\nuse A17\CmsToolkit\Repositories\Behaviors\\", $activeRepositoryTraits) . ";";

        $stub = str_replace(['{{repositoryClassName}}', '{{modelName}}', '{{repositoryTraits}}', '{{repositoryImports}}'], [$repositoryClassName, $modelName, $activeRepositoryTraitsString, $activeRepositoryTraitsImports], $this->files->get(__DIR__ . '/stubs/repository.stub'));

        $this->files->put(app_path('Repositories/' . $repositoryClassName . '.php'), $stub);

        $this->info('Repository created successfully! Control all the things!');
    }

    private function createController($moduleName = 'items', $modelName = 'Item')
    {
        if (!$this->files->isDirectory(app_path('Http/Controllers/Admin'))) {
            $this->files->makeDirectory(app_path('Http/Controllers/Admin'));
        }

        $controllerClassName = $modelName . 'Controller';

        $stub = str_replace(
            ['{{moduleName}}', '{{controllerClassName}}'], [$moduleName, $controllerClassName], $this->files->get(__DIR__ . '/stubs/controller.stub')
        );

        $this->files->put(app_path('Http/Controllers/Admin/' . $controllerClassName . '.php'), $stub);

        $this->info('Controller created successfully! Add your module name, index and form data!');
    }

    private function createRequest($modelName = 'Item')
    {
        if (!$this->files->isDirectory(app_path('Http/Requests/Admin'))) {
            $this->files->makeDirectory(app_path('Http/Requests/Admin'), 0755, true);
        }

        $requestClassName = $modelName . 'Request';

        $stub = str_replace('{{requestClassName}}', $requestClassName, $this->files->get(__DIR__ . '/stubs/request.stub'));

        $this->files->put(app_path('Http/Requests/Admin/' . $requestClassName . '.php'), $stub);

        $this->info('Form request created successfully! Add some validation rules!');
    }

    private function createViews($moduleName = 'items', $translatable = false)
    {
        $viewsPath = config('view.paths')[0] . '/admin/' . $moduleName;

        if (!$this->files->isDirectory($viewsPath)) {
            $this->files->makeDirectory($viewsPath, 0755, true);
        }

        $this->files->put($viewsPath . '/index.blade.php', $this->files->get(__DIR__ . '/stubs/index.blade.stub'));

        $formView = $translatable ? 'form_translatable' : 'form';

        $this->files->put($viewsPath . '/form.blade.php', $this->files->get(__DIR__ . '/stubs/' . $formView . '.blade.stub'));

        $this->info('Views created successfully! Customize all the things!');
    }
}
