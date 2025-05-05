<?php

namespace App\Repository;

use App\Entity\Phone;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Phone>
 *
 * @method Phone|null find($id, $lockMode = null, $lockVersion = null)
 * @method Phone|null findOneBy(array $criteria, array $orderBy = null)
 * @method Phone[]    findAll()
 * @method Phone[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PhoneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Phone::class);
    }

    /**
     * Find all phone numbers for a specific user
     *
     * @param User $user
     * @return Phone[]
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find a verified phone number for a user
     *
     * @param User $user
     * @return Phone|null
     */
    public function findVerifiedPhoneForUser(User $user): ?Phone
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.user = :user')
            ->andWhere('p.verified = true')
            ->setParameter('user', $user)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Add a new phone number for a user
     *
     * @param Phone $phone
     * @param bool $flush
     */
    public function save(Phone $phone, bool $flush = false): void
    {
        $this->getEntityManager()->persist($phone);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Remove a phone number
     *
     * @param Phone $phone
     * @param bool $flush
     */
    public function remove(Phone $phone, bool $flush = false): void
    {
        $this->getEntityManager()->remove($phone);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
