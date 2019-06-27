<?php
/**
 * Photo upload listener.
 */

namespace App\EventListener;

use App\Entity\Photo;
use App\Service\FileUploader;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class PhotoUploadListener.
 */
class PhotoUploadListener
{
    /**
     * Uploader service.
     *
     * @var \App\Service\FileUploader|null
     */
    protected $uploaderService = null;

    /**
     * PhotoUploadListener constructor.
     *
     * @param \App\Service\FileUploader $fileUploader File uploader service
     */
    public function __construct(FileUploader $fileUploader)
    {
        $this->uploaderService = $fileUploader;
    }

    /**
     * Pre persist.
     *
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args Event args
     *
     * @throws \Exception
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();

        $this->uploadFile($entity);
    }

    /**
     * Pre update.
     *
     * @param \Doctrine\ORM\Event\PreUpdateEventArgs $args Event args
     *
     * @throws \Exception
     */
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getEntity();

        $this->uploadFile($entity);
    }

    /**
     * Upload file.
     *
     * @param \App\Entity\Photo $entity Photo entity
     *
     * @throws \Exception
     */
    private function uploadFile($entity): void
    {
        if (!$entity instanceof Photo) {
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
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args Event args
     *
     * @throws \Exception
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof Photo) {
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
}
