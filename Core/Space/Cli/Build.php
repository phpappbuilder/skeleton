<?php

namespace Core\Space\Cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Core\Space\Builder;

class Build extends Command
{
    protected function configure()
    {
        $this
            // имя команды (часть после "bin/console")
            ->setName('space:build')

            // краткое описание, отображающееся при запуске "php bin/console list"
            ->setDescription('Build spaces for paths')

            // полное описание команды, отображающееся при запуске команды
            // с опцией "--help"
            ->setHelp('Build spaces for the path specified in the argument')

            // создать аргумент
            ->addArgument('path', InputArgument::REQUIRED, 'This is the path that will be searched bundles.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Build Your Apps',
            '===============',
            'Path : '.$input->getArgument('path'),
        ]);

        $a = new Builder();
        $c = $a->Build($input->getArgument('path'));
        $output->writeln("Collection Qti : ".count($c[1]));
        $output->writeln("Key Qti : ".count($c[2]));
        $output->writeln("Bundles :");
        for ($i=0;$i<count($c[0]);$i++)
            {
                $output->writeln("[".$i."]". " - " . $c[0][$i]);
            }
    }
}