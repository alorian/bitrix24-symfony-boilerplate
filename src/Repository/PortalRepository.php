<?php

namespace App\Repository;

use App\Entity\Portal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Portal|null find($id, $lockMode = null, $lockVersion = null)
 * @method Portal|null findOneBy(array $criteria, array $orderBy = null)
 * @method Portal[]    findAll()
 * @method Portal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PortalRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Portal::class);
    }

    public function findOneByMemberId($member_id): Portal
    {
        $portal = $this->createQueryBuilder('p')
            ->andWhere('p.member_id = :val')
            ->setParameter('val', $member_id)
            ->getQuery()
            ->getOneOrNullResult();

        if($portal === null) {
            $portal = new Portal();
        }

        return $portal;
    }

}
