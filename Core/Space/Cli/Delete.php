<?php

namespace Core\Space\Cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Core\Space\Builder;

class Delete extends Command
{
    protected function configure()
    {
        $this
            // имя команды (часть после "bin/console")
            ->setName('space:delete')

            // краткое описание, отображающееся при запуске "php bin/console list"
            ->setDescription('Delete spaces for paths')

            // полное описание команды, отображающееся при запуске команды
            // с опцией "--help"
            ->setHelp('Delete spaces for the path specified in the argument')

            // создать аргумент
            ->addArgument('path', InputArgument::REQUIRED, 'This is the path that will be searched bundles.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Delete Spaces',
            '=============',
            'Path : '.$input->getArgument('path'),
        ]);

        $a = new Builder();
        $c = $a->DeletePath($input->getArgument('path'));
        foreach ($c as $key => $value)
            {
                $output->writeln("<------------------------------->");
                $output->writeln("File : [".$key."]");
                $output->writeln("Delete Qt : [".count($value)."]");

            }
    }
}