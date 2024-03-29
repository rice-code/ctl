<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Rice\Ctl\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Tests\Entity\Speak;

class AccessorTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testGenerated(): void
    {
        $dirPath = __DIR__ . DIRECTORY_SEPARATOR . 'Entity' . DIRECTORY_SEPARATOR;
        $input   = new ArrayInput([
            'command' => 'rice:accessor',
            'path'    => [$dirPath . 'Cat.php'],
        ]);
        $output = new NullOutput();
        $app    = (new Application());
        $app->setAutoExit(false);
        $app->run($input);

        $arr = [];
        foreach (['Cat.php', 'BeforeCat.php', 'AfterCat.php'] as $name) {
            $arr[] = file_get_contents($dirPath . $name);
        }
        file_put_contents($dirPath . 'Cat.php', $arr[1]);

        // 格式化后的 token 都是 \n 换行符
        $afterCat = str_replace("\r", '', $arr[2]);
        $this->assertEquals($arr[0], $afterCat);
    }
}
