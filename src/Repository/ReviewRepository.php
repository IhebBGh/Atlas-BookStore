<?php

namespace App\Repository;

use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function findByProduct($product): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.product = :product')
            ->setParameter('product', $product)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getAverageRating($product): float
    {
        $result = $this->createQueryBuilder('r')
            ->select('AVG(r.rating) as avgRating')
            ->where('r.product = :product')
            ->setParameter('product', $product)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? (float) $result : 0.0;
    }

    public function getRatingCount($product): int
    {
        return $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.product = :product')
            ->setParameter('product', $product)
            ->getQuery()
            ->getSingleScalarResult();
    }
}

