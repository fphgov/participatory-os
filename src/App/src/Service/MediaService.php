<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Media;
use App\Entity\MediaInterface;
use Aws\ResultInterface;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\UploadedFile;
use Psr\Http\Message\StreamInterface;

use function basename;
use function unlink;

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

    public function putFileWithStore(
        UploadedFile $file,
        bool $useClientFilename = false
    ): Media {
        $filename = $this->getFilename($file, $useClientFilename);
        $date = new DateTime();

        $this->putFile($file, $useClientFilename);

        $media = new Media();
        $media->setFilename($filename);
        $media->setType($file->getClientMediaType());
        $media->setCreatedAt($date);
        $media->setUpdatedAt($date);

        $this->em->persist($media);
        $this->em->flush();

        return $media;
    }

    public function putFile(
        UploadedFile $file,
        bool $useClientFilename = false
    ): void {
        $filename = $this->getFilename($file, $useClientFilename);

        $this->objectStorage->getClient()->putObject([
            'Bucket'      => self::BUCKET_NAME,
            'Key'         => $filename,
            'Body'        => $file->getStream(),
            'ContentType' => $file->getClientMediaType()
        ]);

        unlink($file->getStream()->getMetadata()['uri']);
    }

    private function getFilename(
        UploadedFile $file,
        bool $useClientFilename = false
    ): string {
        $filename = $useClientFilename ?
            $file->getClientFilename() :
            basename($file->getStream()->getMetadata()['uri']);

        return $filename;
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
