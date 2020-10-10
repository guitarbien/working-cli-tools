<?php

namespace App\Github;

/**
 * Class Exception
 * @package App\Github
 */
final class Exception extends \Exception
{
    /**
     * @return static
     */
    public static function createFail()
    {
        return new static('新增失敗');
    }
}
