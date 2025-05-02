<?php

namespace App\Repository;

use App\Entity\Injuries;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Injuries>
 *
 * @method Injuries|null find($id, $lockMode = null, $lockVersion = null)
 * @method Injuries|null findOneBy(array $criteria, array $orderBy = null)
 * @method Injuries[]    findAll()
 * @method Injuries[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InjuriesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Injuries::class);
    }

    public function findActiveInjuriesByTeam(int $teamId): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.team = :teamId')
            ->andWhere('i.status = :status')
            ->setParameter('teamId', $teamId)
            ->setParameter('status', 'ACTIVE')
            ->orderBy('i.injuryDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPlayerInjuries(int $playerId): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.player = :playerId')
            ->setParameter('playerId', $playerId)
            ->orderBy('i.injuryDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findRecentInjuries(int $days = 30): array
    {
        $date = new \DateTime();
        $date->modify("-{$days} days");

        return $this->createQueryBuilder('i')
            ->andWhere('i.injuryDate >= :date')
            ->setParameter('date', $date)
            ->orderBy('i.injuryDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.status = :status')
            ->setParameter('status', $status)
            ->orderBy('i.injuryDate', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
