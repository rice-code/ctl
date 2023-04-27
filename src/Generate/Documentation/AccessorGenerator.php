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
use Symfony\Component\Console\Tester\TesterTrait;

class AccessorGenerator extends Generator
{
    protected const CLASS_TOKENS                 = [T_CLASS, T_TRAIT, T_INTERFACE, T_ABSTRACT];
    public const ACCESS_PATTERN                  = '/@method\s+\S+\s+([sg]et)(\S+)\(/ux';
    public const REPLACE_PATTERN                 = '/@method\s*(.*)/';
    public const AUTO_REGISTER_SINGLETON_PATTERN = '/use\s+AutoRegisterSingleton;/';

    protected array $lines;
    protected bool $hasAutoRegisterSingleton = false;

    /**
     * @throws ReflectionException
     * @return void
     */
    public function apply(): void
    {
        // 判断是否存在 autoRegisterSingleton  trait 类
        $this->hasAutoRegisterSingleton();

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
                $this->tokens[$idx] = new Token([T_DOC_COMMENT, $this->updateDoc($this->tokens[$idx], $this->hasAutoRegisterSingleton)]);

                continue;
            }
            $this->tokens->insertAt($index, [new Token([T_DOC_COMMENT, $this->getCommentBlock($this->lines, $this->hasAutoRegisterSingleton)])]);
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
            if (!$property->type) {
                continue;
            }
            $docComment      = $this->getBlockComment($property);

            // 框架变量不用添加提示函数
            if ($this->skipFrameVars($property->name)) {
                continue;
            }

            $name     = ucfirst($property->name);

            $lines[$name]['set'] = sprintf(
                '@method self set%s(%s $value) %s',
                $name,
                $property->type,
                $docComment
            );
            $lines[$name]['get'] = sprintf(
                '@method %s get%s()',
                $property->type,
                $name
            );
        }

        return $lines;
    }

    public function hasAutoRegisterSingleton(): void
    {
        preg_match(self::AUTO_REGISTER_SINGLETON_PATTERN, $this->tokens->generateCode(), $matches);

        if (count($matches) > 0) {
            $this->hasAutoRegisterSingleton = true;
        }
    }

    public function updateDoc(Token $token, $hasStatic): string
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
            if ($hasStatic) {
                $firstArr[$len++] = ' * ' . str_replace('@method', '@method static', $line['set']) . PHP_EOL;
            }
            $firstArr[$len++] = ' * ' . $line['get'] . PHP_EOL;
            if ($hasStatic) {
                $firstArr[$len++] = ' * ' . str_replace('@method', '@method static', $line['get']) . PHP_EOL;
            }
        }

        return implode('', array_merge($firstArr, $secondArr));
    }

    /**
     * @param $match
     * @return bool
     */
    private function skipFrameVars($match): bool
    {
        return class_exists(FrameEntity::class) && FrameEntity::inFilter($match);
    }

    /**
     * 获取注释说明.
     *
     * @param Property $property
     * @return string
     */
    public function getBlockComment(Property $property): string
    {
        $lines = (new DocBlock($property->docComment))->getLines();

        $docComment = '';
        foreach ($lines as $line) {
            if ($line->isTheStart() || $line->isTheEnd()) {
                continue;
            }
            if ($line->containsATag()) {
                break;
            }
            $docComment .= ltrim($line->getContent(), " \t\n\r\0\x0B*");
        }

        return rtrim($docComment);
    }
}
