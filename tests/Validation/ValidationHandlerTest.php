<?php declare(strict_types=1);

namespace Tests\Az\Validation;

use Az\Validation\ValidationHandler;
use HttpSoft\Message\StreamFactory;
use HttpSoft\Message\UploadedFileFactory;
use PHPUnit\Framework\TestCase;

final class ValidationHandlerTest extends TestCase
{
    private $validator;

    public function setUp(): void
    {
        $this->validator = new ValidationHandler();        
    }

    public function test_Is_callable()
    {
        $this->assertTrue($this->validator->_is_callable('username'));
        $this->assertTrue($this->validator->_is_callable('regexp'));
        $this->assertFalse($this->validator->_is_callable('notExistFunction'));
    }

    public function testHexColor()
    {
        $this->assertTrue($this->validator->hex_color('#012abc'));
        $this->assertTrue($this->validator->hex_color('#ABCDEF'));
        $this->assertTrue($this->validator->hex_color('#ABC'));
        $this->assertFalse($this->validator->hex_color('#ABCDEG'));
    }

    public function testRegexp()
    {
        $this->assertTrue($this->validator->regexp('Hello', '/\w/'));
        $this->assertFalse($this->validator->regexp('Hello', '/\d/'));
        $this->assertTrue($this->validator->username('John Иванов'));
        $this->assertFalse($this->validator->username('John <Иванов;'));
    }

    public function testFilter()
    {
        $this->assertTrue($this->validator->filter(0755, FILTER_VALIDATE_INT));
        $this->assertFalse($this->validator->filter('0755', FILTER_VALIDATE_INT));
    }

    public function testConfirm()
    {
        $data = ['password' => 'qwerty'];

        $this->assertTrue($this->validator->confirm('qwerty', $data));
        $this->assertFalse($this->validator->confirm('12345', $data));
    }

    public function testLength()
    {
        $this->assertTrue($this->validator->length('qwerty', 3, 8));
        $this->assertFalse($this->validator->length('qwerty', 3, 5));
    }

    public function testMinLength()
    {
        $this->assertTrue($this->validator->minLength('qwerty', 5));
        $this->assertFalse($this->validator->minLength('qwerty', 7));
    }

    public function testMaxLength()
    {
        $this->assertTrue($this->validator->maxLength('qwerty', 7));
        $this->assertFalse($this->validator->maxLength('qwerty', 5));
    }

    public function testMaxWordsCount()
    {
        $str = 'Дорогой John, это было восхитительно!';
        $this->assertTrue($this->validator->maxWordsCount($str, 7));
        $this->assertTrue($this->validator->maxWordsCount($str, 5));
        $this->assertFalse($this->validator->maxWordsCount($str, 4));
    }

    public function testMinValue()
    {
        $this->assertTrue($this->validator->minValue(5, 5));
        $this->assertFalse($this->validator->minValue(5, 7));
    }

    public function testMaxValue()
    {
        $this->assertTrue($this->validator->maxValue(5, 5));
        $this->assertFalse($this->validator->maxValue(7, 5));
    }

    public function testRequired()
    {
        $this->assertTrue($this->validator->required('foo'));
        $this->assertFalse($this->validator->required(''));
        $this->assertFalse($this->validator->required(null));
    }

    public function testValidDate()
    {
        $this->assertTrue($this->validator->validDate('2024-06-01'));
        $this->assertFalse($this->validator->validDate('2024-06-31'));
        $this->assertFalse($this->validator->validDate('01.06.2024'));
    }

    public function testInRange()
    {
        $this->assertTrue($this->validator->inRange(15, 0, 21));
        $this->assertTrue($this->validator->inRange(0, 0, 21));
        $this->assertFalse($this->validator->inRange(0, 12, 21));
    }

    public function testInList()
    {
        $this->assertTrue($this->validator->inList('foo', 'bar', 'foo', 123));
        $this->assertFalse($this->validator->inList('foo', 'bar', 123));
    }

    public function testInArray()
    {
        $this->assertTrue($this->validator->inArray('foo', ['bar', 'foo', 123]));
        $this->assertFalse($this->validator->inArray('foo', ['bar', 123]));
    }

    public function testNotEmpty()
    {
        $stream = (new StreamFactory())->createStream();

        $file = (new UploadedFileFactory)->createUploadedFile($stream);
        $this->assertTrue($this->validator->notEmpty($file));

        $file = (new UploadedFileFactory)->createUploadedFile($stream, null, 4);
        $this->assertFalse($this->validator->notEmpty($file));
    }

    public function testSize()
    {
        $stream = (new StreamFactory())->createStream('Hello, world!!!');
        $file = (new UploadedFileFactory)->createUploadedFile($stream);

        $this->assertTrue($this->validator->size($file, '1M'));
        $this->assertTrue($this->validator->size($file, '1k'));
        $this->assertTrue($this->validator->size($file, 15));
        $this->assertFalse($this->validator->size($file, 14));
    }

    public function testMime()
    {
        $stream = (new StreamFactory())->createStream('Hello, world!!!');
        $file = (new UploadedFileFactory)->createUploadedFile($stream, null, UPLOAD_ERR_OK, null, 'text/plain');

        $this->assertTrue($this->validator->mime($file, 'text/plain'));
        $this->assertFalse($this->validator->mime($file, 'text/html'));
    }

    public function testExt()
    {
        $stream = (new StreamFactory())->createStream('Hello, world!!!');
        $file = (new UploadedFileFactory)->createUploadedFile($stream, null, UPLOAD_ERR_OK, 'file.txt');

        $this->assertTrue($this->validator->ext($file, 'txt', 'php'));
        $this->assertFalse($this->validator->ext($file, 'html'));
    }

    public function testType()
    {
        $stream = (new StreamFactory())->createStream('Hello, world!!!');
        $file = (new UploadedFileFactory)->createUploadedFile($stream, null, UPLOAD_ERR_OK, 'file.txt', 'text/plain');

        $this->assertTrue($this->validator->type($file, 'text'));
        $this->assertFalse($this->validator->ext($file, 'image'));
    }

    public function testImg()
    {
        $stream = (new StreamFactory())->createStream();

        $file = (new UploadedFileFactory)->createUploadedFile($stream, null, UPLOAD_ERR_OK, 'file.png', 'image/png');
        $this->assertTrue($this->validator->img($file));

        $file = (new UploadedFileFactory)->createUploadedFile($stream, null, UPLOAD_ERR_OK, 'file.png', 'text/plain');
        $this->assertFalse($this->validator->img($file));
    }
}
