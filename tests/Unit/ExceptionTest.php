<?php

namespace Tests\Unit;

use App\Github\Exception;
use Tests\TestCase;

/**
 * Class ExceptionTest
 * @package Tests\Unit
 */
class ExceptionTest extends TestCase
{
    public function testCreateFail()
    {
        // arrange
        // act
        $exception = Exception::createFail();

        // assert
        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertEquals('新增失敗', $exception->getMessage());
    }
}
