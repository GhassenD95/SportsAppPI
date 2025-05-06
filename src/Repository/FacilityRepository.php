<?php

namespace App\Repository;

use App\Entity\Facility;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @extends ServiceEntityRepository<Facility>
 */
class FacilityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Facility::class);
    }

    /**
     * @param string|null $name
     * @param string|null $location
     * @param array|null $sports
     * @param bool|null $managedByMe
     * @param \Symfony\Component\Security\Core\User\UserInterface|null $currentUser
     * @param int $page
     * @param int $limit
     * @return Paginator Returns a Paginator object
     */
    public function findByFilters(?string $name, ?string $location, ?array $sports, ?bool $managedByMe, ?\Symfony\Component\Security\Core\User\UserInterface $currentUser, int $page = 1, int $limit = 9): Paginator
    {
        $qb = $this->createQueryBuilder('f')
            ->orderBy('f.name', 'ASC');

        if ($name) {
            $qb->andWhere('LOWER(f.name) LIKE LOWER(:name)')
                ->setParameter('name', '%' . $name . '%');
        }

        if ($location) {
            $qb->andWhere('LOWER(f.location) LIKE LOWER(:location)')
                ->setParameter('location', '%' . $location . '%');
        }

        if ($managedByMe && $currentUser) {
            $qb->andWhere('f.manager = :managerId')
               ->setParameter('managerId', $currentUser->getId());
        }

        if ($sports && !empty($sports)) {
            // For arrays in SQL, we need to handle them carefully.
            // This example assumes sports are stored in a way that LIKE can work,
            // or for a JSON array, specific functions would be needed.
            // If sports is a simple array field and you want to match any sport in the list:
            $sportConditions = [];
            foreach ($sports as $key => $sport) {
                $paramName = 'sport' . $key;
                // This uses a trick with MEMBER OF if sports is a collection, or a custom function/method for arrays.
                // A common way if sports is an array of strings is to check for each sport individually.
                // This example assumes you might be searching for a facility that has AT LEAST ONE of the selected sports.
                // For an exact match or "facility has ALL selected sports", the logic would be more complex.
                // The current card logic suggests `facility.sports` is an array.
                // Doctrine doesn't have a direct `CONTAINS` for simple array types in DQL out of the box.
                // A common workaround is multiple LIKE or a custom DQL function.
                // For simplicity, this example will use multiple LIKE clauses with OR.
                // Note: This is not the most efficient for very large datasets or many selected sports.
                $qb->andWhere($qb->expr()->like('f.sports', ':' . $paramName))
                   ->setParameter($paramName, '%"' . $sport . '"%'); // Assumes sports are stored like ["sport1", "sport2"]
            }
        }
        
        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($qb->getQuery(), true);
    }

    //    /**
    //     * @return Facility[] Returns an array of Facility objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Facility
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
