<?php

namespace Tests\Entity;

/**
 * @method self setEyes(string $value) 眼睛.
 * @method string getEyes()
 * @method self setSpeak(S $value)
 * @method S getSpeak()
 * @method self setHair(string $value)
 * @method string getHair()
 */
class Cat
{
    /**
     * 眼睛.
     *
     * @var string
     */
    protected $eyes;

    /**
     * @var S
     */
    protected $speak;

    /**
     * @var []string
     */
    protected $hair;
}
