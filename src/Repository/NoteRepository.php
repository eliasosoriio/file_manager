<?php

namespace App\Repository;

use App\Entity\Note;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Note>
 */
class NoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Note::class);
    }

    /**
     * Get the single note (we only store one note for simplicity)
     */
    public function getNote(): Note
    {
        $note = $this->findOneBy([]);

        if (!$note) {
            $note = new Note();
            $note->setContent('');
        }

        return $note;
    }

    public function save(Note $note): void
    {
        $em = $this->getEntityManager();

        // Si la nota no tiene ID, es nueva y necesita persistirse
        if (!$note->getId()) {
            $em->persist($note);
        }

        $em->flush();
    }
}
