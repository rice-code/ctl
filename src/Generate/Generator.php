<?php

namespace Rice\Ctl\Generate;

use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\Analyzer\NamespacesAnalyzer;
use Symfony\Component\Filesystem\Exception\IOException;

abstract class Generator
{
    /**
     * 文件路径.
     *
     * @var string
     */
    public string $filePath;

    /**
     * 文件token.
     *
     * @var Tokens
     */
    protected Tokens $tokens;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;

        if (!file_exists($this->filePath)) {
            throw new IOException('file not exists');
        }

        $content      = file_get_contents($this->filePath);
        $this->tokens = Tokens::fromCode($content);
    }

    protected function getCommentBlock($lines, $hasStatic): string
    {
        $comment = '/**' . PHP_EOL;

        foreach ($lines as $line) {
            $comment .= rtrim(' * ' . $line['set']) . PHP_EOL;
            if ($hasStatic) {
                $comment .= rtrim(' * ' . str_replace('@method', '@method static', $line['set'])) . PHP_EOL;
            }
            $comment .= rtrim(' * ' . $line['get']) . PHP_EOL;
            if ($hasStatic) {
                $comment .= rtrim(' * ' . str_replace('@method', '@method static', $line['get'])) . PHP_EOL;
            }
        }

        return $comment . ' */' . PHP_EOL;
    }

    protected function getNamespace()
    {
        return (new NamespacesAnalyzer())->getDeclarations($this->tokens);
    }

    public function getClassName()
    {
        $maxLen = count($this->tokens);
        foreach ($this->tokens as $idx => $token) {
            /**
             * @var Token $token
             */
            if (!$token->isGivenKind(T_CLASS)) {
                continue;
            }

            while ($idx < $maxLen) {
                if (T_WHITESPACE !== $this->tokens[++$idx]->getId()) {
                    return $this->tokens[$idx]->getContent();
                }
            }

            throw new \RuntimeException('this file not class');
        }
    }

    abstract public function apply();
}
