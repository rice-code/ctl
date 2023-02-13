<?php

namespace Rice\Ctl\Generate;

use Exception;
use PhpCsFixer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

class FixGenerate
{
    /**
     * @throws Exception
     */
    public static function handle($dirPath): void
    {
        $app = (new Application());
        $app->setAutoExit(false);
        $app->run(new ArrayInput([
            'command' => 'fix',
            'path'    => [$dirPath],
        ]));
    }
}
