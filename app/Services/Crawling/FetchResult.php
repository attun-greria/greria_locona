<?php

namespace App\Services\Crawling;

/**
 * 取得結果DTO（CRW-002）。
 */
class FetchResult
{
    public function __construct(
        public readonly bool $ok,
        public readonly ?int $status,
        public readonly ?string $body,
        public readonly string $contentType = 'html',  // html / pdf / other
        public readonly ?string $charset = null,
        public readonly ?string $error = null,
    ) {}

    public static function success(int $status, string $body, string $contentType = 'html', ?string $charset = null): self
    {
        return new self(true, $status, $body, $contentType, $charset);
    }

    public static function failure(?int $status, string $error): self
    {
        return new self(false, $status, null, 'html', null, $error);
    }

    /** 取得本文のSHA-256ハッシュ（差分検知用 CRW-006） */
    public function hash(): ?string
    {
        return $this->body !== null ? hash('sha256', $this->body) : null;
    }
}
