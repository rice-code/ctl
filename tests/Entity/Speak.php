<?php

namespace Tests\Entity;

/**
 * @method self setSize(string $value)
 * @method string getSize()
 * @method self setLanguage(string $value) 语言
 * @method string getLanguage()
 */
class Speak
{
    protected string $size;

    /**
     * 语言
     *
     * @var string
     */
    protected $language;

    public function text(): string
    {
        return 'hello, world!';
    }
}
