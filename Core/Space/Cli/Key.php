<?php

namespace Core\Space\Cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Core\Space\Builder;

class Key extends Command
{
    protected function configure()
    {
        $this
            // имя команды (часть после "bin/console")
            ->setName('space:key:choose')

            // краткое описание, отображающееся при запуске "php bin/console list"
            ->setDescription('Shows possible key values')

            // полное описание команды, отображающееся при запуске команды
            // с опцией "--help"
            ->setHelp('Shows possible key values, and can choose a new value from the proposed')

            // создать аргумент
            ->addArgument('key', InputArgument::REQUIRED, 'This is space.')


            ->addArgument('id', InputArgument::OPTIONAL, 'This is choose id.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Key',
            '===============',
            'Space : '.$input->getArgument('key'),
        ]);

        $a = new Builder();


        $lastName = $input->getArgument('id');
        if (!is_numeric ($lastName)) {
            $output->writeln("to select, enter the command > php cli space:key:choose ".$input->getArgument('key')." {choose_id} <");
            $a = $a->GetValues($input->getArgument('key'));
            for ($i=0;$i<count($a);$i++)
                {
                    if ($a[$i]['status']) {$checked = " [checked]";} else {$checked="";}
                    $output->writeln("[".$i."] => ".$a[$i]['name'].$checked);
                }
        }
        else
        {
            $z = $a->GetValues($input->getArgument('key'));
            if (isset($z[$input->getArgument('id')]))
                {
                    if($a->SelectValue($input->getArgument('key') , $input->getArgument('id')))
                        {
                            $output->writeln("[".$input->getArgument('id')."] => choosed" );
                        }
                    else
                        {
                            $output->writeln("Err");
                        }
                }
        }

    }
}