<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\User;
use App\Entity\Wishlist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class WishlistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wishlist::class);
    }

    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('w')
            ->where('w.user = :user')
            ->setParameter('user', $user)
            ->orderBy('w.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByUserAndProduct(User $user, Product $product): ?Wishlist
    {
        return $this->createQueryBuilder('w')
            ->where('w.user = :user')
            ->andWhere('w.product = :product')
            ->setParameter('user', $user)
            ->setParameter('product', $product)
            ->getQuery()
            ->getOneOrNullResult();
    }
}

