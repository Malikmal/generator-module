<?php
/**
 * Created by PhpStorm.
 * User: ahechevarria
 * Date: 18/01/19
 * Time: 12:17
 */

namespace Modules\Generator\Console\Module;

use Modules\Generator\Generators\ModuleGenerator;
use Nwidart\Modules\Commands\ModuleMakeCommand as NwidartModuleMakeCommand;
use Nwidart\Modules\Contracts\ActivatorInterface;

class ModuleMakeCommand extends NwidartModuleMakeCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'generate:module';

    /**
     * Execute the console command.
     */
    public function handle() : int
    {
        $names = $this->argument('name');

        if (!$names) {
            $this->error('No module name(s) provided!');
        }
        $success = true;

        foreach ($names as $name) {
            $code = with(new ModuleGenerator($name))
                ->setFilesystem($this->laravel['files'])
                ->setModule($this->laravel['modules'])
                ->setConfig($this->laravel['config'])
                ->setActivator($this->laravel[ActivatorInterface::class])
                ->setConsole($this)
                ->setForce($this->option('force'))
                ->setPlain($this->option('plain'))
                ->setActive(!$this->option('disabled'))
                ->generate();

            if ($code === E_ERROR) {
                $success = false;
            }
        }

        return $success ? 0 : E_ERROR;
    }
}
