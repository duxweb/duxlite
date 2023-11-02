<?php
declare(strict_types=1);

namespace Dux\Package;

use Nette\Utils\FileSystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class PackageInstallCommand extends Command
{

    protected static $defaultName = 'package:install';
    protected static $defaultDescription = 'Installation package';

    protected function configure(): void
    {
        $this->addArgument(
            'name',
            InputArgument::REQUIRED,
            'please enter the package name'
        );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');

        $helper = $this->getHelper('question');

        $auth = Package::auth($helper, $input, $output);
        if (!is_array($auth)) {
            return $auth;
        }
        [$username, $password] = $auth;

        try {
            [$name, $ver] = explode(':', $name);
            Add::main($output, $username, $password, [$name => $ver ?: 'last']);
        } finally {
            FileSystem::delete(data_path('package'));
        }

        $application = $this->getApplication();
        Package::installOther($application, $output);

        return Command::SUCCESS;
    }

}