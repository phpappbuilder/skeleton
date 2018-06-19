<?php

namespace Core\Space\Cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Core\Space\Builder;

class Collection extends Command
{
    protected function configure()
    {
        $this
            // имя команды (часть после "bin/console")
            ->setName('space:collection:status')

            // краткое описание, отображающееся при запуске "php bin/console list"
            ->setDescription('Return the collection')

            // полное описание команды, отображающееся при запуске команды
            // с опцией "--help"
            ->setHelp('Returns the collection, and changes the status of the element')

            // создать аргумент
            ->addArgument('key', InputArgument::REQUIRED, 'This is space.')


            ->addArgument('id', InputArgument::OPTIONAL, 'This is choose id.')


            ->addArgument('status', InputArgument::OPTIONAL, 'Ellement status.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Collection',
            '===============',
            'Space : '.$input->getArgument('key'),
        ]);

        $a = new Builder();
        $lastName = $input->getArgument('id');
        if (!is_numeric ($lastName)) {
            $output->writeln("To change the status > php cli space:collection:status ".$input->getArgument('key')." {choose_id} {status (enabled) or (disabled)} <");
            $a = $a->ListCollection($input->getArgument('key'));
            for ($i=0;$i<count($a);$i++)
            {
                if ($a[$i]['status']) {$checked = " [enabled]";} else {$checked=" [disabled]";}
                $output->writeln("[".$i."] => ".$a[$i]['name'].$checked);
            }
        }
        else
        {
            $status = $input->getArgument('status');
            if ($status=="enabled" or $status=="disabled")
                {
                    if ($status=="enabled") {$status=true;}
                    else{if ($status=="disabled") {$status=false;}}

                    $z = $a->ListCollection($input->getArgument('key'));
                    if (isset($z[$input->getArgument('id')]))
                    {
                        if($a->CollectionItemStatus($input->getArgument('key') , $input->getArgument('id') , $status))
                        {
                            $output->writeln(var_export($status));
                            if ($status) {$output->writeln("[".$input->getArgument('id')."] => enabled");}
                            else {$output->writeln("[".$input->getArgument('id')."] => disabled");}
                        }
                        else
                        {
                            $output->writeln("Err");
                        }
                    }
                }
            else
                {
                    $output->writeln("Err status");
                }

        }

    }
}