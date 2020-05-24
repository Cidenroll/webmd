<?php

namespace App\Services;

use Aws\S3\S3Client;
use League\Flysystem\AdapterInterface;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Asset\Context\RequestStackContext;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploaderHelper
{

    public const PDF_LOCATION = 'pdfs/confirmed';

    /**
     * @var FilesystemInterface
     */
    private $filesystem;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var RequestStackContext
     */
    private $requestStackContext;
    private $uploadsBaseUrl;

    /**
     * UploaderHelper constructor.
     * @param FilesystemInterface $publicUploadFileSystem
     * @param LoggerInterface $logger
     * @param RequestStackContext $requestStackContext
     * @param string $uploadsBaseUrl
     */
    public function __construct(FilesystemInterface $publicUploadFileSystem, LoggerInterface $logger, RequestStackContext $requestStackContext, string $uploadsBaseUrl)
    {
        $this->filesystem = $publicUploadFileSystem;
        $this->logger = $logger;
        $this->requestStackContext = $requestStackContext;
        $this->uploadsBaseUrl = $uploadsBaseUrl;
    }


    public function upload(UploadedFile $file, int $currentUserId)
    {
        $originalFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $fileA = $currentUserId."_".$originalFileName."_".uniqid(true, true).".".$file->guessExtension();
        $newFileName = sprintf("%s/%s",
            self::PDF_LOCATION,
            $fileA
        );


        $stream = fopen($file->getPathname(), 'rb');
        try {
            $this->filesystem->writeStream(
                $newFileName,
                $stream,
            [
                'visibility'    =>  AdapterInterface::VISIBILITY_PUBLIC
            ]);
        } catch (FileExistsException $e) {
            $this->logger->alert(sprintf("The file %s could not be moved to the public filesystem directory", $newFileName));
            return false;
        }

        if (is_resource($stream)) {
            fclose($stream);
        }

        return ['newFileName' => $newFileName, "urlEncName" => urlencode($originalFileName)];

    }

    public function getPublicPath(string $path): string
    {
        $fullPath = $this->uploadsBaseUrl."/".$path;
        if (strpos($fullPath, "://")!==false) {
            return $fullPath;
        }
        return $this->requestStackContext->getBasePath().$path;
    }

    /**
     * @param string $path
     * @return resource
     */
    public function readStream(string $path)
    {
        try {
            $resource = $this->filesystem->readStream($path);
        } catch (FileNotFoundException $e) {

        }

        if ($resource === false) {
            throw new \Exception(sprintf('Error opening stream for "%s"', $path));
        }

        return $resource;
    }
}