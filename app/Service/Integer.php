<?php


namespace App\Service;


class Integer
{
    /**
     * @var int
     */
    private $value;

    public static function newInstance(int $value)
    {
        return (new static())->setValue($value);
    }

    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param int $value
     * @return static
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    public function __toString()
    {
        return (string) $this->value;
    }
}