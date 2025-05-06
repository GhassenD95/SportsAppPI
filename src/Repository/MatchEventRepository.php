<?php

namespace App\Repository;

use App\Entity\MatchEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MatchEvent>
 */
class MatchEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MatchEvent::class);
    }

    public function findUpcoming(int $limit = 5): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.date > :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('m.date', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find the single next upcoming match for a given team ID (home or away).
     */
    public function findOneUpcomingByTeam(int $teamId): ?MatchEvent
    {
        return $this->createQueryBuilder('m')
            ->where('m.homeTeam = :teamId') // Only check the homeTeam relation
            ->andWhere('m.date > :now')
            ->setParameter('teamId', $teamId)
            ->setParameter('now', new \DateTime())
            ->orderBy('m.date', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    //    /**
    //     * @return MatchEvent[] Returns an array of MatchEvent objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?MatchEvent
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
