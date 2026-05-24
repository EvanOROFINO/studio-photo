<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Video;
use PHPUnit\Framework\TestCase;

class VideoTest extends TestCase
{
    public function testYoutubeStandardUrlIsParsed(): void
    {
        $v = new Video();
        $v->setUrl('https://www.youtube.com/watch?v=dQw4w9WgXcQ');
        $v->parseUrl();

        $this->assertSame(Video::SOURCE_YOUTUBE, $v->getSource());
        $this->assertSame('dQw4w9WgXcQ', $v->getExternalId());
        $this->assertStringContainsString('youtube-nocookie.com/embed/dQw4w9WgXcQ', $v->getEmbedUrl());
        $this->assertStringContainsString('img.youtube.com/vi/dQw4w9WgXcQ', $v->getThumbnailUrl());
    }

    public function testYoutubeShortUrlIsParsed(): void
    {
        $v = new Video();
        $v->setUrl('https://youtu.be/9bZkp7q19f0');
        $v->parseUrl();

        $this->assertSame(Video::SOURCE_YOUTUBE, $v->getSource());
        $this->assertSame('9bZkp7q19f0', $v->getExternalId());
    }

    public function testYoutubeShortsUrlIsParsed(): void
    {
        $v = new Video();
        $v->setUrl('https://www.youtube.com/shorts/abcdef12345');
        $v->parseUrl();

        $this->assertSame(Video::SOURCE_YOUTUBE, $v->getSource());
        $this->assertSame('abcdef12345', $v->getExternalId());
    }

    public function testVimeoUrlIsParsed(): void
    {
        $v = new Video();
        $v->setUrl('https://vimeo.com/123456789');
        $v->parseUrl();

        $this->assertSame(Video::SOURCE_VIMEO, $v->getSource());
        $this->assertSame('123456789', $v->getExternalId());
        $this->assertStringContainsString('player.vimeo.com/video/123456789', $v->getEmbedUrl());
    }

    public function testUnrecognisedUrlLeavesExternalIdEmpty(): void
    {
        $v = new Video();
        $v->setUrl('https://example.com/some-page');
        $v->parseUrl();

        $this->assertNull($v->getExternalId());
        $this->assertNull($v->getEmbedUrl());
    }
}
