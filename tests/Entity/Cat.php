<?php

namespace Tests\Entity;

/**
 * @method self setEyes(string $value)
 * @method string getEyes()
 * @method self setEat(Eat $value)
 * @method Eat getEat()
 * @method self setSpeak(S $value)
 * @method S getSpeak()
 * @method self setHair(string[] $value)
 * @method string[] getHair()
 */
class Cat
{
    /**
     * 眼睛.
     *
     * @return $this
     *
     * @throws \Exception
     *
     * @var string
     * @Param $class
     */
    protected $eyes;

    /**
     * @var Eat
     */
    protected $eat;

    /**
     * @var S
     */
    protected $speak;

    /**
     * @var string[]
     */
    protected $hair;
}