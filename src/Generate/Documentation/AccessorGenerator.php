<?php

namespace Rice\Ctl\Generate\Documentation;

use Exception;
use PhpCsFixer\Preg;
use ReflectionException;
use PhpCsFixer\Tokenizer\Token;
use Rice\Ctl\Generate\Generator;
use PhpCsFixer\DocBlock\DocBlock;
use Rice\Ctl\Generate\Properties\Property;
use Rice\Ctl\Generate\Properties\Properties;
use Rice\Basic\components\Entity\FrameEntity;

class AccessorGenerator extends Generator
{
    protected const CLASS_TOKENS        = [T_CLASS, T_TRAIT, T_INTERFACE, T_ABSTRACT];
    public const ACCESS_PATTERN         = '/@method\s+\S+\s+([sg]et)(\S+)\(/ux';
    public const REPLACE_PATTERN        = '/@method\s*(.*\))/';

    protected $lines;

    protected $docMap;

    public function apply()
    {
        $this->lines = $this->generateLines();

        for ($index = 0, $limit = \count($this->tokens); $index < $limit; ++$index) {
            /**
             * @var Token $token
             */
            $token = $this->tokens[$index];

            if (!$token->isGivenKind(self::CLASS_TOKENS)) {
                continue;
            }

            $idx = $this->tokens->getPrevTokenOfKind($index, [[T_DOC_COMMENT]]);

            if (null !== $idx) {
                $this->tokens[$idx] = new Token([T_DOC_COMMENT, $this->updateDoc($this->tokens[$idx])]);

                continue;
            }
            $this->tokens->insertAt($index, [new Token([T_DOC_COMMENT, $this->getCommentBlock($this->lines)])]);
        }

        file_put_contents($this->filePath, $this->tokens->generateCode());
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function generateLines(): array
    {
        $namespace  = sprintf(
            '%s\%s',
            $this->getNamespace()[0]->getFullName(),
            $this->getClassName()
        );

        $properties = new Properties($namespace);
        $lines      = [];
        foreach ($properties->getProperties() as $property) {
            /**
             * @var Property $property
             */
            $propertyDocType = $this->getDocPropertyType($property->docComment);

            // 框架变量不用添加提示函数
            if ($this->skipFrameVars($property->name)) {
                continue;
            }

            $name = ucfirst($property->name);

            $lines[$name]['set'] = sprintf('@method self set%s($value)', $name);
            $lines[$name]['get'] = sprintf('@method get%s()', $name);

            if ('' !== $propertyDocType || !is_null($property->type)) {
                $typeName            = $property->type ? $property->name : $propertyDocType;
                $lines[$name]['set'] = sprintf(
                    '@method self set%s(%s $value)',
                    $name,
                    $typeName
                );
                $lines[$name]['get'] = sprintf(
                    '@method %s get%s()',
                    $typeName,
                    $name
                );

                continue;
            }
        }

        return $lines;
    }

    public function updateDoc(Token $token): string
    {
        $doc   = new DocBlock($token->getContent());
        $lines = $doc->getLines();
        $len   = count($lines) - 1;
        foreach ($lines as $idx => $line) {
            if (!$line->containsUsefulContent()) {
                continue;
            }

            $len = $idx + 1;
            Preg::match(self::ACCESS_PATTERN, $line->getContent(), $matchs);

            if (empty($matchs)) {
                continue;
            }

            $content                             = Preg::replace(self::REPLACE_PATTERN, $this->lines[$matchs[2]][$matchs[1]], $line->getContent());
            $this->lines[$matchs[2]][$matchs[1]] = trim($content, " \t\n\r\0\x0B*");
            $line->setContent('');
        }

        [$firstArr, $secondArr] = array_chunk($lines, $len);

        foreach ($this->lines as $line) {
            $firstArr[$len++] = ' * ' . $line['set'] . PHP_EOL;
            $firstArr[$len++] = ' * ' . $line['get'] . PHP_EOL;
        }

        return implode('', array_merge($firstArr, $secondArr));
    }

    /**
     * @param $doc
     * @param string[]|string $types
     * @return string
     */
    public function getDocPropertyType($doc, $types = 'var'): string
    {
        if (false === $doc) {
            return '';
        }

        $docBlock = new DocBlock($doc);

        $newTypes = '';

        foreach ($docBlock->getAnnotationsOfType($types) as $annotation) {
            $newTypes = implode('|', $annotation->getTypes());
        }

        return $newTypes;
    }

    /**
     * @param $match
     * @return bool
     */
    private function skipFrameVars($match): bool
    {
        return class_exists(FrameEntity::class) && FrameEntity::inFilter($match);
    }
}
