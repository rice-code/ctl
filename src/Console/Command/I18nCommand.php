<?php

namespace Rice\Ctl\Console\Command;

use Exception;
use Rice\Ctl\Generate\I18nGenerate;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class I18nCommand extends Command
{
    protected static $defaultName = 'rice:i18n';

    // the command description shown when running "php bin/console list"
    protected static $defaultDescription = 'i18n cache generation.';

    // ...
    protected function configure(): void
    {
        $this->setDefinition([
            new InputArgument('input_path', InputArgument::REQUIRED, 'input file/dir path.'),
            new InputArgument('output_path', InputArgument::REQUIRED, 'output file/dir path.'),
            new InputArgument('namespace', InputArgument::REQUIRED, 'namespace file/dir path.'),
        ])
            ->setHelp('This command allows you to create a setting getting function');
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputPath  = $input->getArgument('input_path');
        $outputPath = $input->getArgument('output_path');
        $namespace  = $input->getArgument('namespace');

        $namespace = rtrim($namespace, '\\') . '\\';
        (new I18nGenerate())->handle($inputPath, $outputPath, $namespace);
        $output->write('done.');

        return Command::SUCCESS;
    }
}
