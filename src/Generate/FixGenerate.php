<?php

namespace Rice\Ctl\Generate;

use PhpCsFixer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

class FixGenerate
{
    public static function handle($dirPath)
    {
        $app = (new Application());
        $app->setAutoExit(false);
        $app->run(new ArrayInput([
            'command' => 'fix',
            'path'    => [$dirPath],
        ]));
    }
}
