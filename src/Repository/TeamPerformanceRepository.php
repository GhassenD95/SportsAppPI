<?php

namespace App\Repository;

use App\Entity\TeamPerformance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TeamPerformance>
 *
 * @method TeamPerformance|null find($id, $lockMode = null, $lockVersion = null)
 * @method TeamPerformance|null findOneBy(array $criteria, array $orderBy = null)
 * @method TeamPerformance[]    findAll()
 * @method TeamPerformance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamPerformanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeamPerformance::class);
    }

    public function findTeamPerformances(int $teamId): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.team = :teamId')
            ->setParameter('teamId', $teamId)
            ->orderBy('t.performanceDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findRecentPerformances(int $days = 30): array
    {
        $date = new \DateTime();
        $date->modify("-{$days} days");

        return $this->createQueryBuilder('t')
            ->andWhere('t.performanceDate >= :date')
            ->setParameter('date', $date)
            ->orderBy('t.performanceDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findTopTeams(int $limit = 10): array
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.rating', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findBestDefensiveTeams(int $limit = 10): array
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.goalsConceded', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findBestOffensiveTeams(int $limit = 10): array
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.goalsScored', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
} 