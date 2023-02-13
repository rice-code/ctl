<?php

namespace Rice\Ctl\Generate;

class FileGenerate
{
    protected $jsonFilePath;

    protected $dirPath;

    protected $tpl = null;

    /**
     * Create a new command instance.
     *
     * @param $jsonFilePath
     * @param $dirPath
     */
    public function __construct($jsonFilePath, $dirPath)
    {
        $this->jsonFilePath = $jsonFilePath;
        $this->dirPath      = $dirPath;
        $this->tpl          = file_get_contents(
            __DIR__.DIRECTORY_SEPARATOR.'..'. DIRECTORY_SEPARATOR . 'Template' . DIRECTORY_SEPARATOR . 'Class.php.tpl'
        );
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $jsonObj = json_decode(file_get_contents($this->jsonFilePath), false);

        $this->generateDTOFile($jsonObj);
    }

    protected function generateDTOFile($obj, $className = 'Alias', $type = 'DTO', $namespace = 'Tests\Generate'): void
    {
        $className     = $obj->_class_name ?? $className;
        $type          = $obj->_type       ?? $type;
        $namespace     = $obj->_namespace  ?? $namespace;

        unset($obj->_class_name, $obj->_type, $obj->_namespace);

        $fields = [];
        foreach ($obj as $k => $v) {
            $fields = $this->getFields($v, $className, $k, $type, $namespace, $fields);
        }

        $res = str_replace(
            [
                '{$type}',
                '{$namespace}',
                '{$className}',
                '{$properties}',
            ],
            [
                $type,
                $namespace,
                $className . $type,
                $this->generateProperties($fields),
            ],
            $this->tpl);

        file_put_contents($this->dirPath . $className . $type . '.php', $res);
    }

    protected $propertyfmt = <<<EOF
/**
 * @var %s
 */
protected $%s;
EOF;

    private function generateProperties(array $fields): string
    {
        $str = '';
        foreach ($fields as $field) {
            $str .= sprintf($this->propertyfmt, $field['type'], $field['name']);
            $str .= PHP_EOL . PHP_EOL;
        }

        return $str;
    }

    /**
     * @param $v
     * @param $className
     * @param $k
     * @param $type
     * @param $namespace
     * @param array $fields
     * @return array
     */
    public function getFields($v, $className, $k, $type, $namespace, array $fields): array
    {
        switch (gettype($v)) {
            case 'object':
                $this->generateDTOFile($v, $className . ucfirst($k), $type, $namespace);
                $fields[] = [
                    'type' => $className . ucfirst($k) . $type,
                    'name' => $k,
                ];

                break;
            case 'boolean':
            case 'integer':
            case 'double':
            case 'string':
                $fields[] = [
                    'type' => gettype($v),
                    'name' => $k,
                ];

                break;
            case 'array':
                $fields[] = [
                    'type' => 'array',
                    'name' => $k,
                ];

                if (!empty($v) && is_object($v[0])) {
                    $this->generateDTOFile($v[0], $className . ucfirst($k), $type, $namespace);
                }

                break;
        }
        return $fields;
    }
}
