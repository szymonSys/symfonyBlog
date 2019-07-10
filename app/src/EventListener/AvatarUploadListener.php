<?php
/**
 * Avatar upload listener.
 */

namespace App\EventListener;

use App\Entity\Avatar;
use App\Service\FileUploader;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Exception;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class AvatarUploadListener.
 */
class AvatarUploadListener
{
    /**
     * Uploader service.
     *
     * @var FileUploader|null
     */
    protected $uploaderService = null;

    /**
     * Filesystem.
     *
     * @var |null
     */
    protected $filesystem = null;

    /**
     * PhotoUploadListener constructor.
     *
     * @param FileUploader $fileUploader File uploader service
     * @param Filesystem   $filesystem   Filesystem
     */
    public function __construct(FileUploader $fileUploader, Filesystem $filesystem)
    {
        $this->uploaderService = $fileUploader;
        $this->filesystem = $filesystem;
    }

    /**
     * Pre persist.
     *
     * @param LifecycleEventArgs $args Event args
     *
     * @throws Exception
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();

        $this->uploadFile($entity);
    }

    /**
     * Pre update.
     *
     * @param PreUpdateEventArgs $args Event args
     *
     * @throws Exception
     */
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getEntity();

        $this->uploadFile($entity);
    }

    /**
     * Upload file.
     *
     * @param Avatar $entity Avatar entity
     *
     * @throws Exception
     */
    private function uploadFile($entity): void
    {
        if (!$entity instanceof Avatar) {
            return;
        }

        $file = $entity->getFile();
        if ($file instanceof UploadedFile) {
            $filename = $this->uploaderService->upload($file);
            $entity->setFile($filename);
        }
    }

    /**
     * Post load.
     *
     * @param LifecycleEventArgs $args Event args
     *
     * @throws Exception
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof Avatar) {
            return;
        }

        if ($fileName = $entity->getFile()) {
            $entity->setFile(
                new File(
                    $this->uploaderService->getTargetDirectory().'/'.$fileName
                )
            );
        }
    }

    /**
     * Pre remove.
     *
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        $this->removeFile($entity);
    }

    // ...

    /**
     * Remove file from disk.
     *
     * @param Avatar $entity Avatar entity
     */
    private function removeFile($entity): void
    {
        if (!$entity instanceof Avatar) {
            return;
        }

        $file = $entity->getFile();
        if ($file instanceof File) {
            $this->filesystem->remove($file->getPathname());
        }
    }
}
