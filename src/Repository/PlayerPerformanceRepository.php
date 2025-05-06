<?php

namespace App\Repository;

use App\Entity\PlayerPerformance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlayerPerformance>
 *
 * @method PlayerPerformance|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlayerPerformance|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlayerPerformance[]    findAll()
 * @method PlayerPerformance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlayerPerformanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlayerPerformance::class);
    }

    public function findPlayerPerformances(int $playerId): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.player = :playerId')
            ->setParameter('playerId', $playerId)
            ->orderBy('p.performanceDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findTeamPlayerPerformances(int $teamId): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.team = :teamId')
            ->setParameter('teamId', $teamId)
            ->orderBy('p.performanceDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findRecentPerformances(int $days = 30): array
    {
        $date = new \DateTime();
        $date->modify("-{$days} days");

        return $this->createQueryBuilder('p')
            ->andWhere('p.performanceDate >= :date')
            ->setParameter('date', $date)
            ->orderBy('p.performanceDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findTopPerformers(int $limit = 10): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.rating', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find the latest performance record for a given player ID.
     */
    public function findLatestByPlayer(int $playerId): ?PlayerPerformance
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.player = :playerId')
            ->setParameter('playerId', $playerId)
            ->orderBy('p.performanceDate', 'DESC') // Use correct field name
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}