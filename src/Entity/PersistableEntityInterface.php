<?php declare(strict_types=1);

namespace App\Entity;

interface PersistableEntityInterface
{
    public function getId(): int;
    public function isEntityPersisted(): bool;
}
