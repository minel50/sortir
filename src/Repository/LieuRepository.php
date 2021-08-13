<?php

namespace App\Repository;

use App\Entity\Lieu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Lieu|null find($id, $lockMode = null, $lockVersion = null)
 * @method Lieu|null findOneBy(array $criteria, array $orderBy = null)
 * @method Lieu[]    findAll()
 * @method Lieu[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LieuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lieu::class);
    }
    public function getQueryLieuxParVille($ville) {
        return $this->createQueryBuilder('l')
            ->andWhere('l.ville = :ville')
            ->setParameter('ville', $ville);
    }

    public function getLatitude($latitude){
        $queryBuilder = $this->createQueryBuilder('sortie')

           ->addselect('latitude')
            ->from('lieu','l')
            ->innerJoin('l.sortie','sort')
            ->where('l.latitude = :latitude')
            ->setParameter('latitude',$latitude)
                ->getQuery()
                    ->getResult();


    }
    // /**
    //  * @return Lieu[] Returns an array of Lieu objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    public function findOneByName($value): ?Lieu
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.nom = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

}
