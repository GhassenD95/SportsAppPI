<?php

namespace App\Repository;

use App\Entity\Training;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Training>
 */
class TrainingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Training::class);
    }

    public function findOverlappingTrainings(Training $training): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.id != :id OR :id IS NULL')
            ->andWhere('t.team = :team OR t.facility = :facility OR t.coach = :coach')
            ->andWhere('t.startTime < :endTime AND t.endTime > :startTime')
            ->setParameter('id', $training->getId())
            ->setParameter('team', $training->getTeam())
            ->setParameter('facility', $training->getFacility())
            ->setParameter('coach', $training->getCoach())
            ->setParameter('startTime', $training->getStartTime())
            ->setParameter('endTime', $training->getEndTime())
            ->getQuery()
            ->getResult();
    }

    public function countUpcomingTrainings($user): int
    {
        return $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.startTime > :now')
            ->andWhere('t.coach = :user')
            ->setParameter('now', new \DateTime())
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findNextTrainingForUser($user)
    {
        return $this->createQueryBuilder('t')
            ->where('t.startTime > :now')
            ->andWhere('t.team = :team')
            ->setParameter('now', new \DateTime())
            ->setParameter('team', $user->getTeam())
            ->orderBy('t.startTime', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    //    /**
    //     * @return Training[] Returns an array of Training objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Training
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
