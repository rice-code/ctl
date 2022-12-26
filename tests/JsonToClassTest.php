<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Rice\Ctl\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

class JsonToClassTest extends TestCase
{
    public function testJsonToClass(): void
    {
        $app = (new Application());
        $app->setAutoExit(false);
        $dirPath = __DIR__ . DIRECTORY_SEPARATOR . 'Generate' . DIRECTORY_SEPARATOR;
        $app->run(new ArrayInput([
            'command'           => 'rice:json_to_class',
            'json_file_path'    => $dirPath . 'tsconfig.json',
            'dir_path'          => $dirPath,
        ]));

        $this->assertFileExists($dirPath . 'TestDataEntity.php');
        $this->assertFileExists($dirPath . 'TestDataInsightsDataEntity.php');
        $this->assertFileExists($dirPath . 'TestDataInsightsDataValuesEntity.php');
        $this->assertFileExists($dirPath . 'TestDataInsightsEntity.php');
        $this->assertFileExists($dirPath . 'TestDataInsightsPagingEntity.php');
        $this->assertFileExists($dirPath . 'TestEntity.php');
        $this->assertFileExists($dirPath . 'TestPagingCursorsEntity.php');
        $this->assertFileExists($dirPath . 'TestPagingEntity.php');
    }
}
