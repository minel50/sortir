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
    public function getByCampus($nom,$campus,$from,$to, $isOrganisateur, $isInscrit, $isNotInscrit,
                                $isDone, $participant)
    {
        $queryBuilder = $this->createQueryBuilder('s');

        //in this case, it should return no results
        if (!$isOrganisateur && !$isInscrit && !$isNotInscrit) {
            return [];
        }

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
        if ($from) {
            $queryBuilder
                ->andWhere('s.dateHeureDebut >= :from')
                ->setParameter('from', $from);
        }
        if ($to) {
            $toCopy = clone $to;
            $toCopy->modify('+24hours');
            $queryBuilder
                ->andWhere('s.dateHeureDebut <= :to')
                ->setParameter('to', $toCopy);
        }

        //Part of the query : Organisator OR participant OR not participant
        $or = $queryBuilder->expr()->orX();

        if ($isOrganisateur) {
            $or->add($queryBuilder->expr()->eq('s.organisateur', ':organisateur'));
            $queryBuilder->setParameter('organisateur', $participant);
        }

        if ($isInscrit) {
            $or->add($queryBuilder->expr()->isMemberOf(':participant', 's.participants'));
            $queryBuilder->setParameter('participant', $participant);
        }

        if ($isNotInscrit) {
            $or->add($queryBuilder->expr()->not($queryBuilder->expr()->isMemberOf(':notParticipant', 's.participants')));
            $queryBuilder->setParameter('notParticipant', $participant);
        }

        $queryBuilder->andWhere($or);


        //If "isDone" is checked, all events are selected. If "isDone" is not checked, only present or future events
        // are selected :
        if (!$isDone) {
            $queryBuilder
                ->join('s.etat', 'e')
                ->addSelect('e')
                ->andWhere('e.libelle != :done')
                ->setParameter('done', 'Passée')
                ->andWhere('e.libelle != :canceled')
                ->setParameter('canceled', 'Annulée');
        }


        $queryBuilder->orderBy('s.dateHeureDebut');

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
