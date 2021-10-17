<?php
/**
 * Created by PhpStorm.
 * User: ahechevarria
 * Date: 16/01/19
 * Time: 18:20
 */

namespace Modules\Generator\Console\Publish;

use Illuminate\Support\Str;
use InfyOm\Generator\Commands\Publish\LayoutPublishCommand as InfyOmLayoutPublishCommand;
use InfyOm\Generator\Utils\FileUtil;
use Modules\Generator\Common\Traits\BaseCommandTrait;
use Modules\Generator\Common\Traits\PublishBaseCommandTrait;

class LayoutPublishCommand extends InfyOmLayoutPublishCommand
{
    /**
     * Modifying Inheritance for InfyOm BaseCommand class to inject module param
     */
    use BaseCommandTrait;

    /**
     * Modifying Inheritance for InfyOm PublishBaseCommand class to override ConfigGenerator
     */
    use PublishBaseCommandTrait {
        handle as public handleTrait;
    }

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'generate.publish:layout';

    /**
     * @throws \Exception
     */
    public function handle ()
    {
        $this->handleTrait();

        $templatesPath = config(
            'modules.generator.path.templates_dir',
            resource_path('templates/module-generator-templates/')
        ).'scaffold/layouts/app.stub';


        if (!file_exists($templatesPath)) {
            $this->publishScaffoldTemplates();
        }

        parent::handle();

        $this->generateLayout();
    }

    
    private function generateLayout()
    {
        $viewsPath = config('infyom.laravel_generator.path.views', resource_path('views/'));
        $templateType = config('infyom.laravel_generator.templates', 'adminlte-templates');

        $this->createDirectories($viewsPath);

        if ($this->option('localized')) {
            $files = $this->getLocaleViews();
        } else {
            $files = $this->getViews();
        }

        $moduleName = $this->commandData->getOption('module') ?? app('modules')->getUsedNow();
        
        foreach ($files as $stub => $blade) {
            $templateData = get_template('scaffold/'.$stub, $templateType);
            $templateData = fill_template($this->commandData->dynamicVars, $templateData);
            $templateData = str_replace('$MODULE_NAME$', $moduleName, $templateData);
            FileUtil::createFile($viewsPath, $blade, $templateData );
        }
    }

    
    private function createDirectories($viewsPath)
    {
        FileUtil::createDirectoryIfNotExist($viewsPath.'layouts');
        FileUtil::createDirectoryIfNotExist($viewsPath.'auth');

        FileUtil::createDirectoryIfNotExist($viewsPath.'auth/passwords');
        FileUtil::createDirectoryIfNotExist($viewsPath.'auth/emails');
    }

    private function getViews()
    {
        $views = [
            'layouts/app'               => 'layouts/app.blade.php',
            'layouts/sidebar'           => 'layouts/sidebar.blade.php',
            'layouts/datatables_css'    => 'layouts/datatables_css.blade.php',
            'layouts/datatables_js'     => 'layouts/datatables_js.blade.php',
            'layouts/menu'              => 'layouts/menu.blade.php',
            'layouts/home'              => 'home.blade.php',
            'auth/login'                => 'auth/login.blade.php',
            'auth/register'             => 'auth/register.blade.php',
            'auth/passwords/confirm'    => 'auth/passwords/confirm.blade.php',
            'auth/passwords/email'      => 'auth/passwords/email.blade.php',
            'auth/passwords/reset'      => 'auth/passwords/reset.blade.php',
            'auth/emails/password'      => 'auth/emails/password.blade.php',
        ];

        $version = $this->getApplication()->getVersion();
        if (Str::contains($version, '6.')) {
            $verifyView = [
                'auth/verify_6' => 'auth/verify.blade.php',
            ];
        } else {
            $verifyView = [
                'auth/verify' => 'auth/verify.blade.php',
            ];
        }

        $views = array_merge($views, $verifyView);

        return $views;
    }

    
    private function getLocaleViews()
    {
        return [
            'layouts/app_locale'           => 'layouts/app.blade.php',
            'layouts/sidebar_locale'       => 'layouts/sidebar.blade.php',
            'layouts/datatables_css'       => 'layouts/datatables_css.blade.php',
            'layouts/datatables_js'        => 'layouts/datatables_js.blade.php',
            'layouts/menu'                 => 'layouts/menu.blade.php',
            'layouts/home'                 => 'home.blade.php',
            'auth/login_locale'            => 'auth/login.blade.php',
            'auth/register_locale'         => 'auth/register.blade.php',
            'auth/passwords/email_locale'  => 'auth/passwords/email.blade.php',
            'auth/passwords/reset_locale'  => 'auth/passwords/reset.blade.php',
            'auth/emails/password_locale'  => 'auth/emails/password.blade.php',
        ];
    }

    /**
     * Publishes scaffold templates.
     */
    public function publishScaffoldTemplates()
    {
        $templateType = config('modules.generator.templates', 'core-templates');

        $templatesPath = base_path('vendor/dsielab/'.$templateType.'/templates/scaffold');

        $destinationDir = config('modules.generator.path.templates_dir').'scaffold';

        return $this->publishDirectory($templatesPath, $destinationDir, 'module-generator-templates/scaffold', true);
    }
}
