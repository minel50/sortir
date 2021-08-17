<?php

namespace App\Service;

use App\Entity\Sortie;
use App\Repository\EtatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class SortieStateUpdater {
    private $security;
    private $etatRepository;
    private $entityManager;

    public function __construct(
        Security $security,
        EtatRepository $etatRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->security = $security;
        $this->etatRepository = $etatRepository;
        $this->entityManager = $entityManager;
    }

    public function updateState($sorties) {
        $etatCloturee = $this->etatRepository->findOneBy(['libelle' => 'Clôturée']);
        $etatOuverte = $this->etatRepository->findOneBy(['libelle' => 'Ouverte']);

        foreach ($sorties as $sortie) {
            //set etat from 'Ouverte' to 'Clôturée' (with registering date limit)
           if ($sortie->getEtat()->getLibelle() == 'Ouverte'
               && new \DateTime('now') > $sortie->getDateLimiteInscription())
           {
               $sortie->setEtat($etatCloturee);
               $this->entityManager->persist($sortie);
           }

            //set etat from 'Clôturée' to 'Ouverte' (with registering date modified and max participants not reached)
            if ($sortie->getEtat()->getLibelle() == 'Clôturée'
                && new \DateTime('now') < $sortie->getDateLimiteInscription()
                && count($sortie->getParticipants()) < $sortie->getNbInscriptionsMax())
            {
                $sortie->setEtat($etatOuverte);
                $this->entityManager->persist($sortie);
            }
        }
        $this->entityManager->flush();
    }

    public function cancel(Sortie $sortie):bool {
        $etatAnnulee = $this->etatRepository->findOneBy(['libelle' => 'Annulée']);

        //check if user is also organisator...
        if ($sortie->getOrganisateur() == $this->security->getUser()) {
            //setting state to 'Annulée' only possible for state 'Ouverte' or 'Clôturée'
            if ($sortie->getEtat()->getLibelle() == 'Ouverte' || $sortie->getEtat()->getLibelle() == 'Clôturée') {
                $sortie->setEtat($etatAnnulee);
                $this->entityManager->persist($sortie);
                $this->entityManager->flush();
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function publish(Sortie $sortie):bool {
        $etatOuverte = $this->etatRepository->findOneBy(['libelle' => 'Ouverte']);

        //check if user is also organisator...
        if ($sortie->getOrganisateur() === $this->security->getUser()) {
            //setting state to 'Ouverte' while publishing only possible for state 'Créée'
            if ($sortie->getEtat()->getLibelle() == 'Créée') {
                $sortie->setEtat($etatOuverte);
                $this->entityManager->persist($sortie);
                $this->entityManager->flush();
                return true;
            }
        }

        return false;
    }
}