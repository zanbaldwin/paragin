<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question implements PersistableEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private int $maxPoints = 0;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Exam::class, inversedBy: 'questions')]
        private Exam $exam,
        #[ORM\Column(length: 255)]
        private string $name,
    ) {}

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

    public function getExam(): Exam
    {
        return $this->exam;
    }

    public function getMaxPoints(): int
    {
        return $this->maxPoints;
    }

    public function setMaxPoints(int $points): static
    {
        $this->maxPoints = $points;
        return $this;
    }
}
