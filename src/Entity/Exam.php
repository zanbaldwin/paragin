<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\ExamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExamRepository::class)]
class Exam implements PersistableEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sourceFilename = null;

    #[ORM\OneToMany(mappedBy: 'exam', targetEntity: Question::class)]
    private Collection $questions;

    public function __construct(
        #[ORM\Column(length: 255)]
        private string $name,
    ) {
        $this->questions = new ArrayCollection;
    }

    public function getId(): int
    {
        return $this->id ?? throw new UnpersistedEntityException($this);
    }

    public function isEntityPersisted(): bool
    {
        return isset($this->id);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function getSourceFilename(): ?string
    {
        return $this->sourceFilename;
    }

    public function setSourceFilename(?string $source_filename): static
    {
        $this->sourceFilename = $source_filename;
        return $this;
    }
}
