<?php

declare(strict_types=1);

namespace Tests\Az\Helper;

use PHPUnit\Framework\TestCase;
use Sys\Helper\Accept;
use Psr\Http\Message\ServerRequestInterface;

final class AcceptTest extends TestCase
{
    private function mockRequest(array $headers): ServerRequestInterface
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getHeaderLine')
            ->willReturnCallback(
                fn(string $name) => $headers[$name] ?? ''
            );

        return $request;
    }

    // ── type ─────────────────────────────────────────────────────────

    public function testTypeReturnsFullArray(): void
    {
        $request = $this->mockRequest([
            'Accept' => 'text/html,application/json;q=0.9,*/*;q=0.8',
        ]);

        $accept = new Accept($request);
        $result = $accept->type();

        $this->assertIsArray($result);
        $this->assertEquals(1.0, $result['text/html']);
        $this->assertEquals(0.9, $result['application/json']);
        $this->assertEquals(0.8, $result['*/*']);
    }

    public function testTypeReturnsSpecificPart(): void
    {
        $request = $this->mockRequest([
            'Accept' => 'text/html,application/json;q=0.9',
        ]);

        $accept = new Accept($request);
        $result = $accept->type('application/json');

        $this->assertEquals(0.9, $result);
    }

    public function testTypeReturnsZeroForUnknownPart(): void
    {
        $request = $this->mockRequest([
            'Accept' => 'text/html',
        ]);

        $accept = new Accept($request);
        $result = $accept->type('application/xml');

        $this->assertEquals(0.0, $result);
    }

    public function testTypeWithNoAcceptHeader(): void
    {
        $request = $this->mockRequest([]);

        $accept = new Accept($request);
        $result = $accept->type();

        // quality('') возвращает ['' => 1.0]
        $this->assertIsArray($result);
        $this->assertArrayHasKey('', $result);
        $this->assertEquals(1.0, $result['']);
    }

    // ── isPreferredTypeJson ──────────────────────────────────────────

    public function testIsPreferredTypeJsonReturnsTrue(): void
    {
        $request = $this->mockRequest([
            'Accept' => 'text/html;q=0.5,application/json;q=1.0',
        ]);

        $accept = new Accept($request);
        $this->assertTrue($accept->isPreferredTypeJson());
    }

    public function testIsPreferredTypeJsonReturnsFalse(): void
    {
        $request = $this->mockRequest([
            'Accept' => 'text/html;q=1.0,application/json;q=0.5',
        ]);

        $accept = new Accept($request);
        $this->assertFalse($accept->isPreferredTypeJson());
    }

    public function testIsPreferredTypeJsonWhenOnlyJson(): void
    {
        $request = $this->mockRequest([
            'Accept' => 'application/json',
        ]);

        $accept = new Accept($request);
        $this->assertTrue($accept->isPreferredTypeJson());
    }

    public function testIsPreferredTypeJsonWhenOnlyHtml(): void
    {
        $request = $this->mockRequest([
            'Accept' => 'text/html',
        ]);

        $accept = new Accept($request);
        $this->assertFalse($accept->isPreferredTypeJson());
    }

    public function testIsPreferredTypeJsonWhenNoAcceptHeader(): void
    {
        $request = $this->mockRequest([]);

        $accept = new Accept($request);
        $this->assertFalse($accept->isPreferredTypeJson());
    }

    // ── encoding ─────────────────────────────────────────────────────

    public function testEncodingReturnsFullArray(): void
    {
        $request = $this->mockRequest([
            'Accept-Encoding' => 'gzip, deflate;q=0.5, br;q=0.8',
        ]);

        $accept = new Accept($request);
        $result = $accept->encoding();

        $this->assertIsArray($result);
        $this->assertEquals(1.0, $result['gzip']);
        $this->assertEquals(0.5, $result['deflate']);
        $this->assertEquals(0.8, $result['br']);
    }

    public function testEncodingReturnsSpecificPart(): void
    {
        $request = $this->mockRequest([
            'Accept-Encoding' => 'gzip, deflate;q=0.5',
        ]);

        $accept = new Accept($request);
        $result = $accept->encoding('deflate');

        $this->assertEquals(0.5, $result);
    }

    public function testEncodingReturnsZeroForUnknownPart(): void
    {
        $request = $this->mockRequest([
            'Accept-Encoding' => 'gzip',
        ]);

        $accept = new Accept($request);
        $result = $accept->encoding('br');

        $this->assertEquals(0.0, $result);
    }

    public function testEncodingWithNoHeader(): void
    {
        $request = $this->mockRequest([]);

        $accept = new Accept($request);
        $result = $accept->encoding();

        // quality('') возвращает ['' => 1.0]
        $this->assertIsArray($result);
        $this->assertArrayHasKey('', $result);
        $this->assertEquals(1.0, $result['']);
    }

    // ── charset ──────────────────────────────────────────────────────

    public function testCharsetReturnsFullArray(): void
    {
        $request = $this->mockRequest([
            'Accept-Charset' => 'utf-8, iso-8859-1;q=0.5, *;q=0.1',
        ]);

        $accept = new Accept($request);
        $result = $accept->charset();

        $this->assertIsArray($result);
        $this->assertEquals(1.0, $result['utf-8']);
        $this->assertEquals(0.5, $result['iso-8859-1']);
        $this->assertEquals(0.1, $result['*']);
    }

    public function testCharsetReturnsSpecificPart(): void
    {
        $request = $this->mockRequest([
            'Accept-Charset' => 'utf-8, iso-8859-1;q=0.5',
        ]);

        $accept = new Accept($request);
        $result = $accept->charset('iso-8859-1');

        $this->assertEquals(0.5, $result);
    }

    public function testCharsetReturnsZeroForUnknownPart(): void
    {
        $request = $this->mockRequest([
            'Accept-Charset' => 'utf-8',
        ]);

        $accept = new Accept($request);
        $result = $accept->charset('koi8-r');

        $this->assertEquals(0.0, $result);
    }

    public function testCharsetWithNoHeader(): void
    {
        $request = $this->mockRequest([]);

        $accept = new Accept($request);
        $result = $accept->charset();

        // quality('') возвращает ['' => 1.0]
        $this->assertIsArray($result);
        $this->assertArrayHasKey('', $result);
        $this->assertEquals(1.0, $result['']);
    }

    // ── quality ──────────────────────────────────────────────────────

    public function testQualityWithDefaultQuality(): void
    {
        $accept = new Accept($this->mockRequest([]));
        $result = $accept->quality('text/html, application/json');

        $this->assertIsArray($result);
        $this->assertEquals(1.0, $result['text/html']);
        $this->assertEquals(1.0, $result['application/json']);
    }

    public function testQualityWithQualityValues(): void
    {
        $accept = new Accept($this->mockRequest([]));
        $result = $accept->quality('text/html;q=0.9, application/json;q=0.8');

        $this->assertIsArray($result);
        $this->assertEquals(0.9, $result['text/html']);
        $this->assertEquals(0.8, $result['application/json']);
    }

    public function testQualityWithMixedValues(): void
    {
        $accept = new Accept($this->mockRequest([]));
        $result = $accept->quality('text/html, application/json;q=0.7');

        $this->assertIsArray($result);
        $this->assertEquals(1.0, $result['text/html']);
        $this->assertEquals(0.7, $result['application/json']);
    }

    public function testQualityWithSpacesAroundQ(): void
    {
        $accept = new Accept($this->mockRequest([]));
        $result = $accept->quality('text/html; q=0.8, application/json; q=0.9');

        $this->assertIsArray($result);
        $this->assertEquals(0.8, $result['text/html']);
        $this->assertEquals(0.9, $result['application/json']);
    }

    public function testQualityWithDotQualityValue(): void
    {
        $accept = new Accept($this->mockRequest([]));
        $result = $accept->quality('text/html;q=.9');

        $this->assertIsArray($result);
        $this->assertEquals(0.9, $result['text/html']);
    }

    public function testQualityHandlesNewlines(): void
    {
        $accept = new Accept($this->mockRequest([]));
        $result = $accept->quality("text/html\r\n, application/json;q=0.5");

        $this->assertIsArray($result);
        $this->assertEquals(1.0, $result['text/html']);
        $this->assertEquals(0.5, $result['application/json']);
    }

    public function testQualitySingleValue(): void
    {
        $accept = new Accept($this->mockRequest([]));
        $result = $accept->quality('application/json;q=1');

        $this->assertIsArray($result);
        $this->assertEquals(1.0, $result['application/json']);
    }

    public function testQualityEmptyString(): void
    {
        $accept = new Accept($this->mockRequest([]));
        $result = $accept->quality('');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('', $result);
        $this->assertEquals(1.0, $result['']);
    }
}
