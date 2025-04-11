<?php

namespace TheJawker\Mediaux\Contracts;

interface MediaContract
{
    public function getSize(): int;
    public function getMimeType(): string;
    public function getFileExtension(): string;
    public function getHash(): string;
    public function getContent(): ?string;
    public function getTemporaryFilePath(): string;
    public function getInternalFileName(): string;
    public function getUrl(): string;
}
