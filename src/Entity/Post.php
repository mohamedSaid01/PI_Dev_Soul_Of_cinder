<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Title cannot be empty")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Title must be at least {{ limit }} characters long",
        maxMessage: "Title cannot be longer than {{ limit }} characters"
    )]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Description cannot be empty")]
    #[Assert\Length(
        min: 10,
        minMessage: "Description must be at least {{ limit }} characters long"
    )]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Type cannot be empty")]
    #[Assert\Choice(
        choices: ["news", "article", "tutorial", "review"],
        message: "Choose a valid type: news, article, tutorial, or review"
    )]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\File(
        maxSize: "1024k",
        mimeTypes: ["image/jpeg", "image/png", "image/gif"],
        mimeTypesMessage: "Please upload a valid image (JPEG, PNG, GIF)"
    )]
    private ?string $image = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255)]
    private ?string $author = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Category must be selected")]
    private ?PostCategory $category = null;

    #[ORM\Column]
    private ?bool $enabled = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = \DateTimeImmutable::createFromMutable(new \DateTime()); // Convert \DateTime to \DateTimeImmutable
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt instanceof \DateTimeImmutable 
            ? $createdAt 
            : \DateTimeImmutable::createFromMutable($createdAt);
        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getCategory(): ?PostCategory
    {
        return $this->category;
    }

    public function setCategory(?PostCategory $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }
}
