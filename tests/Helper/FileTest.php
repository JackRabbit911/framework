<?php

declare(strict_types=1);

namespace Tests\Az\Helper;

use PHPUnit\Framework\TestCase;
use Sys\Helper\File;

final class FileTest extends TestCase
{
    private File $file;
    private string $tempDir;

    protected function setUp(): void
    {
        $this->file = new File();
        $this->tempDir = sys_get_temp_dir() . '/file_test_' . uniqid();
        mkdir($this->tempDir);
    }

    protected function tearDown(): void
    {
        // cleanup temp files
        array_map('unlink', glob($this->tempDir . '/*'));
        rmdir($this->tempDir);
    }

    // ── mimeByExt ────────────────────────────────────────────────────

    public function testMimeByExtJpg(): void
    {
        $result = $this->file->mimeByExt('jpg');

        $this->assertEquals('image/jpeg', $result);
    }

    public function testMimeByExtPng(): void
    {
        $result = $this->file->mimeByExt('png');

        $this->assertEquals('image/png', $result);
    }

    public function testMimeByExtGif(): void
    {
        $result = $this->file->mimeByExt('gif');

        $this->assertEquals('image/gif', $result);
    }

    public function testMimeByExtJson(): void
    {
        $result = $this->file->mimeByExt('json');

        $this->assertEquals('application/json', $result);
    }

    public function testMimeByExtHtml(): void
    {
        $result = $this->file->mimeByExt('html');

        $this->assertEquals('text/html', $result);
    }

    public function testMimeByExtCss(): void
    {
        $result = $this->file->mimeByExt('css');

        $this->assertEquals('text/css', $result);
    }

    public function testMimeByExtJavascript(): void
    {
        $result = $this->file->mimeByExt('js');

        $this->assertEquals('application/javascript', $result);
    }

    public function testMimeByExtPdf(): void
    {
        $result = $this->file->mimeByExt('pdf');

        $this->assertEquals('application/pdf', $result);
    }

    public function testMimeByExtZip(): void
    {
        $result = $this->file->mimeByExt('zip');

        $this->assertEquals('application/x-zip', $result);
    }

    public function testMimeByExtPhp(): void
    {
        $result = $this->file->mimeByExt('php');

        $this->assertEquals('application/x-httpd-php', $result);
    }

    public function testMimeByExtCaseInsensitive(): void
    {
        $result = $this->file->mimeByExt('JPG');

        $this->assertEquals('image/jpeg', $result);
    }

    public function testMimeByExtUnknownExtension(): void
    {
        $this->expectException(\TypeError::class);

        $this->file->mimeByExt('unknown_ext_xyz');
    }

    // ── extByImageType ───────────────────────────────────────────────

    public function testExtByImageTypeJpeg(): void
    {
        $result = $this->file->extByImageType('image/jpeg');

        $this->assertEquals('.jpg', $result);
    }

    public function testExtByImageTypeJpegWithoutDot(): void
    {
        $result = $this->file->extByImageType('image/jpeg', dot: false);

        $this->assertEquals('jpg', $result);
    }

    public function testExtByImageTypePng(): void
    {
        $result = $this->file->extByImageType('image/png');

        $this->assertEquals('.png', $result);
    }

    public function testExtByImageTypeGif(): void
    {
        $result = $this->file->extByImageType('image/gif');

        $this->assertEquals('.gif', $result);
    }

    public function testExtByImageTypeWebp(): void
    {
        $result = $this->file->extByImageType('image/webp');

        $this->assertEquals('.webp', $result);
    }

    public function testExtByImageTypeUnknown(): void
    {
        $result = $this->file->extByImageType('image/svg+xml');

        $this->assertEquals('', $result);
    }

    public function testExtByImageTypeNullMimeType(): void
    {
        $result = $this->file->extByImageType('application/octet-stream');

        $this->assertEquals('', $result);
    }

    // ── mime ─────────────────────────────────────────────────────────

    public function testMimeByExtensionFromMap(): void
    {
        $file = $this->tempDir . '/test.txt';
        file_put_contents($file, 'plain text');

        $result = $this->file->mime($file);

        $this->assertEquals('text/plain', $result);
    }

    public function testMimeByExtensionFromMapJson(): void
    {
        $file = $this->tempDir . '/test.json';
        file_put_contents($file, '{"key":"value"}');

        $result = $this->file->mime($file);

        $this->assertEquals('application/json', $result);
    }

    public function testMimeByExtensionFromMapCss(): void
    {
        $file = $this->tempDir . '/test.css';
        file_put_contents($file, 'body {}');

        $result = $this->file->mime($file);

        $this->assertEquals('text/css', $result);
    }

    public function testMimeByExtensionFromMapMultipleTypes(): void
    {
        $file = $this->tempDir . '/test.pdf';
        file_put_contents($file, '%PDF-1.4');

        $result = $this->file->mime($file);

        $this->assertEquals('application/pdf', $result);
    }

    public function testMimeForNonExistentFile(): void
    {
        $result = $this->file->mime('/nonexistent/path/file.txt');

        $this->assertEquals('', $result);
    }

    public function testMimeForFileWithoutExtension(): void
    {
        $file = $this->tempDir . '/Makefile';
        file_put_contents($file, 'all:');

        $result = $this->file->mime($file);

        $this->assertEquals('', $result);
    }

    public function testMimeForEmptyFile(): void
    {
        $file = $this->tempDir . '/empty.txt';
        file_put_contents($file, '');

        $result = $this->file->mime($file);

        $this->assertEquals('text/plain', $result);
    }

    public function testMimeForImageWithRealMime(): void
    {
        // Создаём минимальный PNG (1x1 пиксель)
        $file = $this->tempDir . '/test.png';
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
        file_put_contents($file, $png);

        $result = $this->file->mime($file);

        $this->assertEquals('image/png', $result);
    }

    public function testMimeForGifWithRealMime(): void
    {
        // Создаём минимальный GIF
        $file = $this->tempDir . '/test.gif';
        $gif = "GIF89a\x01\x00\x01\x00\x80\x00\x00\xff\xff\xff\x00\x00\x00!\xf9\x04\x00\x00\x00\x00\x00,\x00\x00\x00\x00\x01\x00\x01\x00\x00\x02\x02D\x01\x00;";
        file_put_contents($file, $gif);

        $result = $this->file->mime($file);

        $this->assertEquals('image/gif', $result);
    }

    public function testMimeForJpgWithRealMime(): void
    {
        // Создаём минимальный JPEG
        $file = $this->tempDir . '/test.jpg';
        $jpg = base64_decode('/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////2wBDAf//////////////////////////////////////////////////////////////////////////////////////wAARCAABAAEDASIAAhEBAxEB/8QAFAABAAAAAAAAAAAAAAAAAAAACv/EABQQAQAAAAAAAAAAAAAAAAAAAAD/xAAUAQEAAAAAAAAAAAAAAAAAAAAA/8QAFAEBAAAAAAAAAAAAAAAAAAAAAP/aAAwDAQACEQMRAD8A9NKK8z+07f/9k=');
        file_put_contents($file, $jpg);

        $result = $this->file->mime($file);

        $this->assertEquals('image/jpeg', $result);
    }

    public function testMimeWithSpacesInFilename(): void
    {
        $file = $this->tempDir . '/test file.txt';
        file_put_contents($file, 'content');

        $result = $this->file->mime($file);

        $this->assertEquals('text/plain', $result);
    }

    public function testMimeWithSpecialCharsInFilename(): void
    {
        $file = $this->tempDir . '/test-file_v1.0.json';
        file_put_contents($file, '{}');

        $result = $this->file->mime($file);

        $this->assertEquals('application/json', $result);
    }

    // ── extByType ────────────────────────────────────────────────────

    public function testExtByTypeJpeg(): void
    {
        $result = $this->file->extByType('image/jpeg');

        $this->assertEquals('jpg', $result);
    }

    public function testExtByTypePng(): void
    {
        $result = $this->file->extByType('image/png');

        $this->assertEquals('png', $result);
    }

    public function testExtByTypeGif(): void
    {
        $result = $this->file->extByType('image/gif');

        $this->assertEquals('gif', $result);
    }

    public function testExtByTypeWebp(): void
    {
        $result = $this->file->extByType('image/webp');

        $this->assertEquals('webp', $result);
    }

    public function testExtByTypeSvg(): void
    {
        $result = $this->file->extByType('image/svg+xml');

        $this->assertEquals('svg', $result);
    }

    public function testExtByTypeBmp(): void
    {
        $result = $this->file->extByType('image/bmp');

        $this->assertEquals('bmp', $result);
    }

    public function testExtByTypePdf(): void
    {
        $result = $this->file->extByType('application/pdf');

        $this->assertEquals('pdf', $result);
    }

    public function testExtByTypeDoc(): void
    {
        $result = $this->file->extByType('application/msword');

        $this->assertEquals('doc', $result);
    }

    public function testExtByTypeXls(): void
    {
        $result = $this->file->extByType('application/vnd.ms-excel');

        $this->assertEquals('xls', $result);
    }

    public function testExtByTypeDocx(): void
    {
        $result = $this->file->extByType('application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $this->assertEquals('docx', $result);
    }

    public function testExtByTypeXlsx(): void
    {
        $result = $this->file->extByType('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->assertEquals('xlsx', $result);
    }

    public function testExtByTypeZip(): void
    {
        $result = $this->file->extByType('application/zip');

        $this->assertEquals('zip', $result);
    }

    public function testExtByTypeGzip(): void
    {
        $result = $this->file->extByType('application/gzip');

        $this->assertEquals('gz', $result);
    }

    public function testExtByTypeTar(): void
    {
        $result = $this->file->extByType('application/x-tar');

        $this->assertEquals('tar', $result);
    }

    public function testExtByTypeRar(): void
    {
        $result = $this->file->extByType('application/x-rar-compressed');

        $this->assertEquals('rar', $result);
    }

    public function testExtByTypeMp3(): void
    {
        $result = $this->file->extByType('audio/mpeg');

        $this->assertEquals('mp3', $result);
    }

    public function testExtByTypeWav(): void
    {
        $result = $this->file->extByType('audio/wav');

        $this->assertEquals('wav', $result);
    }

    public function testExtByTypeOgg(): void
    {
        $result = $this->file->extByType('audio/ogg');

        $this->assertEquals('ogg', $result);
    }

    public function testExtByTypeFlac(): void
    {
        $result = $this->file->extByType('audio/flac');

        $this->assertEquals('flac', $result);
    }

    public function testExtByTypeMp4(): void
    {
        $result = $this->file->extByType('video/mp4');

        $this->assertEquals('mp4', $result);
    }

    public function testExtByTypeMkv(): void
    {
        $result = $this->file->extByType('video/x-matroska');

        $this->assertEquals('mkv', $result);
    }

    public function testExtByTypeMov(): void
    {
        $result = $this->file->extByType('video/quicktime');

        $this->assertEquals('mov', $result);
    }

    public function testExtByTypeWebm(): void
    {
        $result = $this->file->extByType('video/webm');

        $this->assertEquals('webm', $result);
    }

    public function testExtByTypeHtml(): void
    {
        $result = $this->file->extByType('text/html');

        $this->assertEquals('html', $result);
    }

    public function testExtByTypeCss(): void
    {
        $result = $this->file->extByType('text/css');

        $this->assertEquals('css', $result);
    }

    public function testExtByTypeJs(): void
    {
        $result = $this->file->extByType('application/javascript');

        $this->assertEquals('js', $result);
    }

    public function testExtByTypeJson(): void
    {
        $result = $this->file->extByType('application/json');

        $this->assertEquals('json', $result);
    }

    public function testExtByTypeXml(): void
    {
        $result = $this->file->extByType('text/xml');

        $this->assertEquals('xml', $result);
    }

    public function testExtByTypeTxt(): void
    {
        $result = $this->file->extByType('text/plain');

        $this->assertEquals('txt', $result);
    }

    public function testExtByTypeCsv(): void
    {
        $result = $this->file->extByType('text/csv');

        $this->assertEquals('csv', $result);
    }

    public function testExtByTypeWoff(): void
    {
        // font/woff нет в массиве
        $result = $this->file->extByType('font/woff');

        $this->assertEquals('', $result);
    }

    public function testExtByTypeWoff2(): void
    {
        // font/woff2 нет в массиве
        $result = $this->file->extByType('font/woff2');

        $this->assertEquals('', $result);
    }

    public function testExtByTypeUnknown(): void
    {
        $result = $this->file->extByType('application/octet-stream');

        $this->assertEquals('', $result);
    }

    public function testExtByTypeEmptyString(): void
    {
        $result = $this->file->extByType('');

        $this->assertEquals('', $result);
    }
}
