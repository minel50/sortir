<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }


    public function search($nom){
    $queryBuilder = $this->createQueryBuilder('s');
    if($nom!=null){
        $queryBuilder
            ->andWhere('s.nom  LIKE  :nom')
            ->setParameter('nom',$nom);
    }
    return $queryBuilder->getQuery()->getResult();


}
    public function getByCampus($nom,$campus,$from,$to)
    {
        $queryBuilder = $this->createQueryBuilder('s');
        if ($nom != '') {

        $queryBuilder
            ->where('s.nom LIKE :nom')
            ->setParameter('nom', "%{$nom}%");
                    }

        if($campus !=''){
            $queryBuilder

              ->andWhere('s.campus = :campus')
                ->setParameter('campus', $campus);

        }
        if($from!=null && $to!=null){
            $queryBuilder
                ->andWhere('s.dateHeureDebut BETWEEN :from AND :to')
                ->setparameter('from',$from)
                ->setParameter('to',$to)   ;
        }

        return $queryBuilder->getQuery()->getResult();


    }








    // /**
    //  * @return Sortie[] Returns an array of Sortie objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Sortie
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
