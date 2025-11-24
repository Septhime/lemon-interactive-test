<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * @return array<Event>
     */
    public function findByDateRange(?\DateTime $start = null, ?\DateTime $end = null): array
    {
        $qb = $this->createQueryBuilder('e');

        if (null === $start && null === $end) {
            // Si aucune date n'est spécifiée, retourner les événements futurs
            $qb->where('e.begin >= :now')
                ->setParameter('now', new \DateTime());
        } else {
            if (null !== $start) {
                $qb->andWhere('e.begin >= :start')
                    ->setParameter('start', $start);
            }
            if (null !== $end) {
                $qb->andWhere('e.begin <= :end')
                    ->setParameter('end', $end);
            }
        }

        $qb->orderBy('e.begin', 'ASC');

        /** @var array<Event> $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    public function save(Event $event, bool $flush = true): void
    {
        $this->getEntityManager()->persist($event);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Event $event, bool $flush = true): void
    {
        $this->getEntityManager()->remove($event);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }
}
