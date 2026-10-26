<?php declare(strict_types=1);

namespace Tests\Az\Validation;

use Az\Validation\Validation;
use Az\Validation\ValidationValue;
use Az\Validation\Parser;
use Az\Validation\Resolver;
use Az\Validation\ValidationHandler;
use Az\Validation\Response;
use Az\Validation\Message;
use PHPUnit\Framework\TestCase;

final class ValidationValueTest extends TestCase
{
    private $validation;

    public function setUp(): void
    {
        $message = new Message();
        $response = new Response($message);
        $validation = new Validation($response);

        $validation->rule('list', 'inList(foo, bar)')
            ->rule('in.arr', 'inArray', ['foo', 'bar'])
            ->rule('len', 'length(3, 8)|is_string')
            ->rule('str', 'is_string');

        $this->validation = new ValidationValue(
            $response,
            $validation->getRules(),
            $validation->data
        );
    }

    public function testCheck()
    {
        $this->assertTrue($this->validation->check('bar', 'list'));
        $this->assertFalse($this->validation->check('ban', 'list'));
        $this->assertTrue($this->validation->check('bar', 'in.arr'));
        $this->assertFalse($this->validation->check('ban', 'in.arr'));
        $this->assertTrue($this->validation->check('hello', 'len'));
        $this->assertFalse($this->validation->check('hello, world!', 'len'));
        $this->assertTrue($this->validation->check('hello', 'str'));
        $this->assertFalse($this->validation->check(123, 'str'));
    }
}
