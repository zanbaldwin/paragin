<?php declare(strict_types=1);

namespace App\Entity;

class UnpersistedEntityException extends \RuntimeException
{
    public function __construct(
        public readonly PersistableEntityInterface $entity,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(sprintf(
            'Cannot fetch database-generated ID of unpersisted entity of type "%s".',
            get_class($entity),
        ), 0, $previous);
    }
}
