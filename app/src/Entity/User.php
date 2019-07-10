<?php
/**
 * User entity.
 */
namespace App\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class User.
 *
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(
 *     name="users",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="email_idx",
 *              columns={"email"},
 *          )
 *     }
 * )
 *
 * @UniqueEntity(fields={"email"})
 */
class User implements UserInterface
{
    const NUMBER_OF_ITEMS = 6;
    /**
     * Role users.
     *
     * @var string
     */
    const ROLE_USER = 'ROLE_USER';

    /**
     * Role admin.
     *
     * @var string
     */
    const ROLE_ADMIN = 'ROLE_ADMIN';

    /**
     * Primary key.
     *
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(
     *     type="integer",
     *     nullable=false,
     *     options={"unsigned"=true},
     * )
     */
    private $id;

    /**
     * Created at.
     *
     * @var DateTime
     *
     * @Gedmo\Timestampable(on="create")
     *
     * @ORM\Column(type="datetime")
     *
     * @Assert\DateTime
     */
    private $createdAt;

    /**
     * Updated at.
     *
     * @var DateTime
     *
     * @Gedmo\Timestampable(on="update")
     *
     * @ORM\Column(type="datetime")
     *
     * @Assert\DateTime
     */
    private $updatedAt;

    /**
     * E-mail.
     *
     * @var string
     *
     * @ORM\Column(
     *     type="string",
     *     length=128,
     * )
     *
     * @Assert\NotBlank
     * @Assert\Email
     */
    private $email;

    /**
     * Password.
     *
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * Roles.
     *
     * @ORM\Column(type="array")
     */
    private $roles = [];

    /**
     * First name.
     *
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank
     * @Assert\Length(
     *     min="3",
     *     max="255",
     * )
     */
    private $firstName;

    /**
     * Blog name.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $blogName;

    /**
     * Articles.
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Article",
     *     mappedBy="author",
     *     orphanRemoval=true
     * )
     */
    private $articles;

    /**
     * Followed authors.
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="followers")
     */
    private $followedAuthors;

    /**
     * Followers.
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="followedAuthors")
     */
    private $followers;

    /**
     * Comments.
     *
     * @var Collection|null
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Comment",
     *     mappedBy="author",
     *     orphanRemoval=true,
     *     fetch="EXTRA_LAZY",
     * )
     */
    private $comments;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(
     *     max="1000",
     * )
     */
    private $bio;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Avatar", mappedBy="user", cascade={"persist", "remove"})
     */
    private $avatar;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->articles = new ArrayCollection();
        $this->followedAuthors = new ArrayCollection();
        $this->followers = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    /**
     * Getter for the Id.
     *
     * @return int|null Result
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Getter for the Created At.
     *
     * @return DateTimeInterface|null Created At
     */
    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * Setter for the Created At.
     *
     * @param DateTimeInterface $createdAt Created At
     */
    public function setCreatedAt(DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Getter for the Updated At.
     *
     * @return DateTimeInterface|null updated at
     */
    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * Setter for the Updated At.
     *
     * @param DateTimeInterface $updatedAt Updated at
     */
    public function setUpdatedAt(DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Getter for the E-mail.
     *
     * @return string|null E-mail
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Setter for the E-mail.
     *
     * @param string $email E-mail
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * {@inheritdoc}
     *
     * @see UserInterface
     *
     * @return string User name
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * Getter for the Password.
     *
     * @return string|null Password
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Setter for the Password.
     *
     * @param string $password Password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * Getter for the Roles.
     *
     * @return array Roles
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = static::ROLE_USER;

        return array_unique($roles);
    }

    /**
     * Setter for the Roles.
     *
     * @param array $roles Roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * Check if admin.
     *
     * @return bool
     */
    public function checkIfAdmin(): bool
    {
        $isAdmin = false;
        foreach ($this->getRoles() as $role) {
            if ('ROLE_ADMIN' === $role) {
                $isAdmin = true;
            }
        }

        return $isAdmin;
    }

    /**
     * Make admin method.
     */
    public function makeAdmin(): void
    {
        $this->roles[] = 'ROLE_ADMIN';
    }

    /**
     * Divest admin method.
     */
    public function divestAdmin(): void
    {
        if (isset($this->roles[1]) && 'ROLE_ADMIN' === $this->roles[1]) {
            unset($this->roles[1]);
        }
    }

    /**
     * Getter for salt.
     *
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using bcrypt or argon
    }

    /**
     * Erase creditionals method.
     *
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the users, clear it here
        // $this->plainPassword = null;
    }

    /**
     * Getter for the First name.
     *
     * @return string|null First name
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * Setter for the First Name.
     *
     * @param string $firstName First Name
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * Getter for blogName.
     *
     * @return string|null
     */
    public function getBlogName(): ?string
    {
        return $this->blogName;
    }

    /**
     * Setter for blogName.
     *
     * @param string|null $blogName
     *
     * @return User
     */
    public function setBlogName(?string $blogName): self
    {
        $this->blogName = $blogName;

        return $this;
    }

    /**
     * Getter for articles.
     *
     * @return Collection|Article[]
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    /**
     * Add action for article.
     *
     * @param Article $article
     *
     * @return User
     */
    public function addArticle(Article $article): self
    {
        if (!$this->articles->contains($article)) {
            $this->articles[] = $article;
            $article->setAuthor($this);
        }

        return $this;
    }

    /**
     * Remove action for article.
     *
     * @param Article $article
     *
     * @return User
     */
    public function removeArticle(Article $article): self
    {
        if ($this->articles->contains($article)) {
            $this->articles->removeElement($article);
            // set the owning side to null (unless already changed)
            if ($article->getAuthor() === $this) {
                $article->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * Getter for followedAuthors.
     *
     * @return Collection|self[]
     */
    public function getFollowedAuthors(): Collection
    {
        return $this->followedAuthors;
    }

    /**
     * Add action for followedAuthor.
     *
     * @param User $followedAuthor
     *
     * @return User
     */
    public function addFollowedAuthor(User $followedAuthor): self
    {
        if (!$this->followedAuthors->contains($followedAuthor)) {
            $this->followedAuthors[] = $followedAuthor;
        }

        return $this;
    }

    /**
     * Remove action for followedAuthor.
     *
     * @param User $followedAuthor
     *
     * @return User
     */
    public function removeFollowedAuthor(User $followedAuthor): self
    {
        if ($this->followedAuthors->contains($followedAuthor)) {
            $this->followedAuthors->removeElement($followedAuthor);
        }

        return $this;
    }

    /**
     * Getter for followers.
     *
     * @return Collection|self[]
     */
    public function getFollowers(): Collection
    {
        return $this->followers;
    }

    /**
     * Add action for follower.
     *
     * @param User $follower
     *
     * @return User
     */
    public function addFollower(User $follower): self
    {
        if (!$this->followers->contains($follower)) {
            $this->followers[] = $follower;
            $follower->addFollowedAuthor($this);
        }

        return $this;
    }

    /**
     * Remove action for follower.
     *
     * @param User $follower
     *
     * @return User
     */
    public function removeFollower(User $follower): self
    {
        if ($this->followers->contains($follower)) {
            $this->followers->removeElement($follower);
            $follower->removeFollowedAuthor($this);
        }

        return $this;
    }

    /**
     * Getter for comments.
     *
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    /**
     * Add action for comment.
     *
     * @param Comment $comment
     *
     * @return User
     */
    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setAuthor($this);
        }

        return $this;
    }

    /**
     * Remove action for comment.
     *
     * @param Comment $comment
     *
     * @return User
     */
    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getAuthor() === $this) {
                $comment->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * Getter for bio.
     *
     * @return string|null
     */
    public function getBio(): ?string
    {
        return $this->bio;
    }

    /**
     * Setter for bio.
     *
     * @param string|null $bio
     *
     * @return User
     */
    public function setBio(?string $bio): self
    {
        $this->bio = $bio;

        return $this;
    }

    /**
     * Getter for avatar.
     *
     * @return Avatar|null
     */
    public function getAvatar(): ?Avatar
    {
        return $this->avatar;
    }

    /**
     * Setter for avatar.
     *
     * @param Avatar|null $avatar
     *
     * @return User
     */
    public function setAvatar(?Avatar $avatar): self
    {
        $this->avatar = $avatar;

        // set (or unset) the owning side of the relation if necessary
        $newUser = null === $avatar ? null : $this;
        if ($newUser !== $avatar->getUser()) {
            $avatar->setUser($newUser);
        }

        return $this;
    }
}
