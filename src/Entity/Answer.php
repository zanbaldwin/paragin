<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\AnswerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnswerRepository::class)]
class Answer implements PersistableEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $source_filename = null;

    #[ORM\OneToMany(mappedBy: 'exam', targetEntity: Question::class)]
    private Collection $questions;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Question::class)]
        private Question $question,
        #[ORM\ManyToOne(targetEntity: Student::class)]
        private Student $student,
        #[ORM\Column]
        private int $points,
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

    public function getQuestion(): Question
    {
        return $this->question;
    }

    public function getStudent(): Student
    {
        return $this->student;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function setPoints(int $points): static
    {
        $this->points = $points;
        return $this;
    }
}
