<?php

namespace Tests\Entity;

/**
 * @method self setEyes(string $value)
 * @method string getEyes()
 * @method self setSpeak(S $value)
 * @method S getSpeak()
 * @method self setHair(string[] $value)
 * @method string[] getHair()
 */
class Cat
{
    /**
     * @var string
     */
    protected $eyes;

    /**
     * @var S
     */
    protected $speak;

    /**
     * @var string[]
     */
    protected $hair;
}
