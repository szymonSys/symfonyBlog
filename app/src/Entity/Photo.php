<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PhotoRepository")
 * @ORM\Table(
 *     name="photos",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="UQ_photos_1",
 *              columns={"file"},
 *          ),
 *     },
 * )
 *
 * @UniqueEntity(
 *     fields={"file"}
 * )
 */
class Photo implements \Serializable
{
    /**
     * Primary key.
     *
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * Created at.
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="create")
     *
     * @Assert\DateTime
     */
    private $createdAt;

    /**
     * Updated at.
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="update")
     *
     * @Assert\DateTime
     */
    private $updatedAt;

    /**
     * File.
     *
     * @ORM\Column(
     *     type="string",
     *     length=191,
     *     nullable=false,
     *     unique=true,
     * )
     *
     * @Assert\NotBlank
     * @Assert\Image(
     *     maxSize = "2048k",
     *     mimeTypes={"image/png", "image/jpeg", "image/pjpeg", "image/jpeg", "image/pjpeg"},
     * )
     */
    private $file;

    /**
     * @ORM\OneToOne(
     *     targetEntity="App\Entity\Article",
     *     inversedBy="coverPhoto",
     *     cascade={"persist", "remove"}
     * )
     *
     * @ORM\JoinColumn(nullable=false)
     */
    private $article;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Getter for File.
     *
     * @return mixed|null File
     */
    public function getFile()
    {
        return $this->file;
    }


    /**
     * Setter for File name.
     *
     * @param string $file File
     */
    public function setFile($file): void
    {
        $this->file = $file;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    /**
     * Getter for Article.
     *
     * @return \App\Entity\Article|null Article entity
     */
    public function setArticle(Article $article): self
    {
        $this->article = $article;

        return $this;
    }

    /**
     * @see \Serializable::serialize()
     *
     * @return string Serialized object
     */
    public function serialize(): string
    {
        $file = $this->getFile();

        return serialize(
            [
                $this->id,
                ($file instanceof File) ? $file->getFilename() : $file,
            ]
        );
    }

    /**
     * @see \Serializable::unserialize()
     *
     * @param string $serialized Serialized object
     */
    public function unserialize($serialized): void
    {
        list($this->id) = unserialize($serialized);
    }
}
