<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\StudentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StudentRepository::class)]
class Student implements PersistableEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function __construct(
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
}
