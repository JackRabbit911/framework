<?php

namespace Sys\Create\Commands;

use Sys\Console\Command;
use Sys\Create\Files;

class CreateFiles extends Command
{
    protected function configure()
    {
        $this
            ->addArgument('name', 'Сommon name for group files')
            ->addArgument('types', 'Types of files: controller, entity etc..', [])
            ->addOption(['interactive', 'i'], 'Interactive mode flag');
    }

    public function execute($name, $types, $opts)
    {
        $info = pathinfo($name);
        $path = $info['dirname'];

        if ($path === '.') {
            $path = 'app';
        }

        $paths = $this->getPaths($path, config('structure'));
        $paths_keys = array_keys($paths);

        $this->validateTypes($types, $paths_keys);

        if ($opts->interactive || empty($types)) {
            $types = array_unique(array_merge($types, $this->checkboxes($paths_keys)));
        }

        $response = (new Files)->create($path, $info['filename'], $types, $paths);

        if (!empty($response['success'])) {
            $str = implode(' ', $response['success']);
            $this->climate->out('The files were successfully created: <light_green>' . $str . '</light_green>');
        }

        if (!empty($response['errors'])) {
            $str = implode(' ', $response['errors']);
            $this->climate->out('Failed to create files and folders: <yellow>' . $str . '</yellow>');
        }

        if (!empty($response['warnings'])) {
            $str = implode(' ', $response['warnings']);
            $this->climate->out('Files already exists: <yellow>' . $str . '</yellow>');
        }
    }

    private function checkboxes($options)
    {
        $input = $this->climate->checkboxes('Please check the boxes as needed', $options);
        return $input->prompt();
    }

    protected function getPaths($path, $config)
    {
        return $config[$path] ?? $config['submodule'] ?? $config['app'];
    }

    private function validateTypes($types, $filesTypes)
    {
        if (!empty($types)) {
            $errors = array_diff($types, $filesTypes);

            if (!empty($errors)) {
                $this->climate->out('<red>WARNING!</red> Invalid arguments: <yellow>' . implode(' ', $errors) . '</yellow>');
                exit;
            }
        }
    }
}
