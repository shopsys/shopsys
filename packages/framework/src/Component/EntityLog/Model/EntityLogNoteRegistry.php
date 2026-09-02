<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\Model;

use Override;
use SplObjectStorage;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Collects notes explaining why entities are about to be changed, so that the logs created during the flush can carry them.
 *
 * Notes are registered for a particular entity instance, because a single flush can log several entities and only some of them
 * are being changed for a reason worth recording.
 */
class EntityLogNoteRegistry implements ResetInterface
{
    /**
     * @var \SplObjectStorage<object, string>
     */
    protected SplObjectStorage $notesByEntity;

    public function __construct()
    {
        $this->notesByEntity = new SplObjectStorage();
    }

    /**
     * The note is used by the very next log created for the given entity, so it has to be registered right before the flush
     */
    public function registerNote(object $entity, string $note): void
    {
        $this->notesByEntity[$entity] = $note;
    }

    public function findNote(object $entity): ?string
    {
        return $this->notesByEntity[$entity] ?? null;
    }

    #[Override]
    public function reset(): void
    {
        $this->notesByEntity = new SplObjectStorage();
    }
}
