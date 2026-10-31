<?php

declare(strict_types=1);

namespace Tests\Az\Helper;

use PHPUnit\Framework\TestCase;
use Sys\Helper\File;

final class ExtByTypeTest extends TestCase
{
    private File $file;

    protected function setUp(): void
    {
        $this->file = new File();
    }

    // ── images ───────────────────────────────────────────────────────

    public function testImageJpeg(): void
    {
        $this->assertEquals('jpg', $this->file->extByType('image/jpeg'));
    }

    public function testImagePng(): void
    {
        $this->assertEquals('png', $this->file->extByType('image/png'));
    }

    public function testImageGif(): void
    {
        $this->assertEquals('gif', $this->file->extByType('image/gif'));
    }

    public function testImageWebp(): void
    {
        $this->assertEquals('webp', $this->file->extByType('image/webp'));
    }

    public function testImageSvg(): void
    {
        $this->assertEquals('svg', $this->file->extByType('image/svg+xml'));
    }

    public function testImageBmp(): void
    {
        $this->assertEquals('bmp', $this->file->extByType('image/bmp'));
    }

    public function testImageTiff(): void
    {
        $this->assertEquals('tiff', $this->file->extByType('image/tiff'));
    }

    public function testImageIco(): void
    {
        $this->assertEquals('ico', $this->file->extByType('image/x-icon'));
    }

    // ── documents ────────────────────────────────────────────────────

    public function testDocumentPdf(): void
    {
        $this->assertEquals('pdf', $this->file->extByType('application/pdf'));
    }

    public function testDocumentDoc(): void
    {
        $this->assertEquals('doc', $this->file->extByType('application/msword'));
    }

    public function testDocumentDocx(): void
    {
        $this->assertEquals('docx', $this->file->extByType('application/vnd.openxmlformats-officedocument.wordprocessingml.document'));
    }

    public function testDocumentXls(): void
    {
        $this->assertEquals('xls', $this->file->extByType('application/vnd.ms-excel'));
    }

    public function testDocumentXlsx(): void
    {
        $this->assertEquals('xlsx', $this->file->extByType('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'));
    }

    public function testDocumentPpt(): void
    {
        $this->assertEquals('ppt', $this->file->extByType('application/vnd.ms-powerpoint'));
    }

    public function testDocumentPptx(): void
    {
        $this->assertEquals('pptx', $this->file->extByType('application/vnd.openxmlformats-officedocument.presentationml.presentation'));
    }

    // ── archives ─────────────────────────────────────────────────────

    public function testArchiveZip(): void
    {
        $this->assertEquals('zip', $this->file->extByType('application/zip'));
    }

    public function testArchiveZipCompressed(): void
    {
        $this->assertEquals('zip', $this->file->extByType('application/x-zip-compressed'));
    }

    public function testArchive7z(): void
    {
        $this->assertEquals('7z', $this->file->extByType('application/x-7z-compressed'));
    }

    public function testArchiveGzip(): void
    {
        $this->assertEquals('gz', $this->file->extByType('application/gzip'));
    }

    public function testArchiveGzipAlt(): void
    {
        $this->assertEquals('gz', $this->file->extByType('application/x-gzip'));
    }

    public function testArchiveTar(): void
    {
        $this->assertEquals('tar', $this->file->extByType('application/x-tar'));
    }

    public function testArchiveRar(): void
    {
        $this->assertEquals('rar', $this->file->extByType('application/x-rar-compressed'));
    }

    // ── audio ────────────────────────────────────────────────────────

    public function testAudioMp3(): void
    {
        $this->assertEquals('mp3', $this->file->extByType('audio/mpeg'));
    }

    public function testAudioWav(): void
    {
        $this->assertEquals('wav', $this->file->extByType('audio/wav'));
    }

    public function testAudioOgg(): void
    {
        $this->assertEquals('ogg', $this->file->extByType('audio/ogg'));
    }

    public function testAudioFlac(): void
    {
        $this->assertEquals('flac', $this->file->extByType('audio/flac'));
    }

    // ── video ────────────────────────────────────────────────────────

    public function testVideoMp4(): void
    {
        $this->assertEquals('mp4', $this->file->extByType('video/mp4'));
    }

    public function testVideoAvi(): void
    {
        $this->assertEquals('avi', $this->file->extByType('video/x-msvideo'));
    }

    public function testVideoWmv(): void
    {
        $this->assertEquals('wmv', $this->file->extByType('video/x-ms-wmv'));
    }

    public function testVideoMkv(): void
    {
        $this->assertEquals('mkv', $this->file->extByType('video/x-matroska'));
    }

    public function testVideoWebm(): void
    {
        $this->assertEquals('webm', $this->file->extByType('video/webm'));
    }

    public function testVideoMov(): void
    {
        $this->assertEquals('mov', $this->file->extByType('video/quicktime'));
    }

    // ── text/code ────────────────────────────────────────────────────

    public function testTextHtml(): void
    {
        $this->assertEquals('html', $this->file->extByType('text/html'));
    }

    public function testTextCss(): void
    {
        $this->assertEquals('css', $this->file->extByType('text/css'));
    }

    public function testTextCsv(): void
    {
        $this->assertEquals('csv', $this->file->extByType('text/csv'));
    }

    public function testTextPlain(): void
    {
        $this->assertEquals('txt', $this->file->extByType('text/plain'));
    }

    public function testTextXml(): void
    {
        $this->assertEquals('xml', $this->file->extByType('text/xml'));
    }

    public function testTextJavascript(): void
    {
        $this->assertEquals('js', $this->file->extByType('application/javascript'));
    }

    public function testTextJson(): void
    {
        $this->assertEquals('json', $this->file->extByType('application/json'));
    }

    public function testTextXhtml(): void
    {
        $this->assertEquals('xhtml', $this->file->extByType('application/xhtml+xml'));
    }

    public function testTextXmlAlt(): void
    {
        $this->assertEquals('xml', $this->file->extByType('application/xml'));
    }

    // ── edge cases ───────────────────────────────────────────────────

    public function testUnknownMimeType(): void
    {
        $this->assertEquals('', $this->file->extByType('application/octet-stream'));
    }

    public function testEmptyMimeType(): void
    {
        $this->assertEquals('', $this->file->extByType(''));
    }

    public function testCaseInsensitive(): void
    {
        $this->assertEquals('jpg', $this->file->extByType('IMAGE/JPEG'));
        $this->assertEquals('png', $this->file->extByType('Image/Png'));
        $this->assertEquals('json', $this->file->extByType('APPLICATION/JSON'));
    }
}
