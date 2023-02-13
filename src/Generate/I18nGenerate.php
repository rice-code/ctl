<?php

namespace Rice\Ctl\Generate;

use ReflectionException;
use Symfony\Component\Filesystem\Filesystem;

class I18nGenerate
{
    protected const COMMENT_MATCH = '/@?([a-zA-Z-]+)\s+(\S+)/';
    protected string $inputPath;
    protected string $outputPath;
    protected string $namespace;
    protected array $paths     = [];
    protected array $areaCodes = [];
    protected array $outputs   = [];

    /**
     * @throws ReflectionException
     */
    public function handle(string $inputPath, string $outputPath, string $namespace): void
    {
        $this->inputPath  = $inputPath;
        $this->outputPath = $outputPath;
        $this->namespace  = $namespace;

        $this->scanDir($this->inputPath, $this->namespace);
        $this->buildCache();
        $this->writeCache();
    }

    /**
     * 扫描目录，查找对应的枚举类文件.
     *
     * @param string $sourcePath
     * @param string $namespace
     * @return void
     */
    public function scanDir(string $sourcePath, string $namespace): void
    {
        $dirs = scandir($sourcePath) ?? [];
        foreach ($dirs as $path) {
            $fullPath = $sourcePath . DIRECTORY_SEPARATOR . $path;
            if (is_dir($fullPath)) {
                if ('.' === $path || '..' === $path) {
                    continue;
                }
                $this->scanDir($fullPath, $this->getStr($namespace) . $path);

                continue;
            }
            preg_match('/(.*)Enum/', $path, $matches);
            if (isset($matches[1]) && !empty($matches[1])) {
                $this->paths[$matches[1]] = [
                    'path'      => $fullPath,
                    'namespace' => $namespace,
                ];
            }
        }
    }

    /**
     * @throws ReflectionException
     */
    public function buildCache(): void
    {
        foreach ($this->paths as $name => $path) {
            $namespace  = $this->getStr($path['namespace']) . $name . 'Enum';
            $reflection = new \ReflectionClass($namespace);
            $constants  = $reflection->getReflectionConstants();

            foreach ($constants as $constant) {
                $doc     = $constant->getDocComment();
                $matches = [];
                preg_match_all(self::COMMENT_MATCH, $doc, $matches, PREG_SET_ORDER, 0);

                foreach ($matches as $match) {
                    $areaCode = $match[1];
                    $desc     = $match[2];
                    if (!in_array($areaCode, $this->areaCodes, true)) {
                        $this->areaCodes[] = $areaCode;
                    }
                    $this->outputs[$areaCode][$name][$constant->getName()] = $desc;
                }
            }
        }
    }

    public function writeCache(): void
    {
        // areaCode -> fileName -> filedName -> desc
        $fileSystem = new Filesystem();
        foreach ($this->outputs as $areaCode => $output) {
            $areaPath = $this->outputPath . DIRECTORY_SEPARATOR . $areaCode;
            if (!$fileSystem->exists($areaPath)) {
                $fileSystem->mkdir($areaPath);
            }

            foreach ($output as $fileName => $item) {
                $filePath = $areaPath . DIRECTORY_SEPARATOR . $fileName . '.json';
                $fileSystem->dumpFile($filePath, json_encode($item, JSON_UNESCAPED_UNICODE));
            }
        }
    }

    /**
     * @param string $namespace
     * @return string
     */
    public function getStr(string $namespace): string
    {
        return rtrim($namespace, '\\') . '\\';
    }
}
