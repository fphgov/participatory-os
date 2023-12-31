<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Media;
use App\Entity\MediaInterface;
use Aws\ResultInterface;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\UploadedFile;
use Psr\Http\Message\StreamInterface;

final class MediaService implements MediaServiceInterface
{
    const BUCKET_NAME = 'shared';

    public function __construct(
        private array $config,
        private EntityManagerInterface $em,
        private MinIOServiceInterface $objectStorage
    ) {
        $this->config        = $config;
        $this->em            = $em;
        $this->objectStorage = $objectStorage;
    }

    public function getMedia(string $id): ?MediaInterface
    {
        $mediaRepository = $this->em->getRepository(Media::class);

        return $mediaRepository->findOneBy(['id' => $id]);
    }

    public function getMediaStream(MediaInterface $media): StreamInterface
    {
        if ($this->config['app']['service']['file'] === "s3") {
            $s3Object = $this->getFile($media->getFilename());

            return $s3Object->get('Body');
        }

        $filePath = $this->config['app']['paths']['files'] . '/' . $media->getFilename();

        return new Stream($filePath);
    }

    public function putFile(UploadedFile $file): void
    {
       $this->objectStorage->getClient()->putObject([
            'Bucket'      => self::BUCKET_NAME,
            'Key'         => $file->getClientFilename(),
            'Body'        => $file->getStream(),
            'ContentType' => $file->getClientMediaType()
        ]);
    }

    public function getFile(string $key): ResultInterface
    {
        $retrive = $this->objectStorage->getClient()->getObject([
            'Bucket' => self::BUCKET_NAME,
            'Key'    => $key,
        ]);

        return $retrive;
    }
}
