<?php
/**
 * File Uploader service.
 */

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class FileUploader.
 */
class FileUploader
{
    /**
     * Target directory.
     *
     * @var string
     */
    protected $targetDirectory;

    /**
     * FileUploader constructor.
     *
     * @param string $targetDirectory Target directory
     */
    public function __construct($targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }

    /**
     * Upload file.
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     *
     * @throws \Exception
     *
     * @return string File name
     */
    public function upload(UploadedFile $file): string
    {
        $fileName = bin2hex(random_bytes(32)).'.'.$file->guessExtension();

        try {
            $file->move($this->targetDirectory, $fileName);
        } catch (FileException $exception) {
            // ... handle exception if something happens during file upload
        }

        return $fileName;
    }

    /**
     * Get target directory.
     *
     * @return string Target directory
     */
    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}
