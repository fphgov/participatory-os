<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\MediaInterface;
use Aws\ResultInterface;
use Psr\Http\Message\StreamInterface;
use Laminas\Diactoros\UploadedFile;

interface MediaServiceInterface
{
    public function getMedia(string $id): ?MediaInterface;

    public function getMediaStream(MediaInterface $media): StreamInterface;

    public function putFile(UploadedFile $file, bool $useClientFilename = false): void;

    public function putFileWithStore(UploadedFile $file, bool $useClientFilename = false): MediaInterface;

    public function getFile(string $key): ResultInterface;
}
