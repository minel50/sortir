<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\SearchDateType;
use App\Form\SearchSortieType;
use App\Form\SortieType;
use App\Form\UpdateSortieType;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use App\Service\SortieStateUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class SortieController extends AbstractController
{
    #[Route('/sortie/create', name: 'sortie_create')]
    public function create(Request $request, EntityManagerInterface $entityManager,EtatRepository $etatRepository,VilleRepository $villeRepository,SortieRepository $sortieRepository): Response
    {
        $sortie = new Sortie();

       // $sortie = $sortieRepository->find(1);
        $ville = $villeRepository->find(1);


        $user = $this->getUser();
        $campus=$user->getCampus();
        $sortieForm = $this->createForm(SortieType::class,$sortie,[
            'ville'=>$ville
        ]);
        $sortieForm->handleRequest($request);
        if($sortieForm->isSubmitted() && $sortieForm->isValid())
        {

            $sortie->setOrganisateur($user);
            $sortie->setCampus($campus);
            $sortie->setEtat($etatRepository->findOneBy(['libelle'=>'Créée']));
            $entityManager->persist($sortie);
            $entityManager->flush();


            return $this->redirectToRoute('sortie_list');

        }



        return $this->render('sortie/create.html.twig', [
            'controller_name' => 'SortieController',
            'sortie'=>$sortie,
            'sortieForm'=>$sortieForm->createView()


        ]);
    }

    #[Route('/sortie/list', name: 'sortie_list')]
    public function list(SortieRepository $sortieRepository,
                         Request $request,
                        SortieStateUpdater $sortieStateUpdater
    ): Response
    {
        $listeForm = $this->createForm(SearchSortieType::class);
        $listeForm->handleRequest($request);

        if($listeForm->isSubmitted() && $listeForm->isValid()) {

            $nom = $listeForm['nom']->getData();
            $campus=$listeForm['campus']->getData();
            $from = $listeForm['from']->getData();
            $to = $listeForm['to']->getData();
            $isOrganisateur = $listeForm['isOrganisateur']->getData();
            $isInscrit = $listeForm['isInscrit']->getData();
            $isNotInscrit = $listeForm['isNotInscrit']->getData();
            $isDone = $listeForm['isDone']->getData();


        } else {    //default values if first loading of the page
            $nom = null;
            $campus = null;     //to change with campus of the logged participant
            $from = null;
            $to = null;
            $isOrganisateur = true;
            $isInscrit = true;
            $isNotInscrit = true;
            $isDone = false;
        }

        $participant = $this->getUser();
        $sorties=$sortieRepository->getByCampus($nom,$campus,$from,$to, $isOrganisateur, $isInscrit, $isNotInscrit,
            $isDone, $participant);

        //Service to update state (for now only switch between 'Ouverte' and 'Clôturée' developed).
        $sortieStateUpdater->updateState($sorties);

        return $this->render('sortie/list.html.twig', [
            'controller_name' => 'SortieController',
            'sorties'=>$sorties,
            'listeForm'=>$listeForm->createView(),
            'isOrganisateur' => $isOrganisateur,
            'isInscrit' => $isInscrit,
            'isNotInscrit' => $isNotInscrit
        ]);
    }


    #[Route('/sortie/update/{id}', name: 'sortie_update')]
    public function update(Request $request,SortieRepository $sortieRepository, int $id,EntityManagerInterface $entityManager,LieuRepository $lieuRepository): Response
    {



        $sortie=$sortieRepository->find($id);
        $updateForm = $this->createForm(UpdateSortieType::class,$sortie);

        $updateForm->handleRequest($request);
        if($updateForm->isSubmitted() && $updateForm->isValid()) {

            $entityManager->persist($sortie);
            $entityManager->flush();


            return $this->redirectToRoute('sortie_list');

        }


        return $this->render('sortie/update.html.twig', [
            'controller_name' => 'SortieController',
            'sortie'=>$sortie,
            'updateForm'=>$updateForm->createView()


        ]);


    }

    #[Route('/sortie/{idSortie}/inscription', name: 'sortie_register', methods: ["GET"])]
    public function register(int $idSortie,
                            SortieRepository $sortieRepository,
                            EtatRepository $etatRepository,
                            EntityManagerInterface $entityManager
    ): Response {
        $sortie = $sortieRepository->find($idSortie);

        //check if register possible (sortie.etat = Ouverte)
        if ($sortie->getEtat()->getLibelle() == "Ouverte") {
            $sortie->addParticipant($this->getUser());

            //if limit of participants reached, registering closure
            if (count($sortie->getParticipants()) >= $sortie->getNbInscriptionsMax()) {
                $etatCloturee = $etatRepository->findOneBy(['libelle' => 'Clôturée']);
                $sortie->setEtat($etatCloturee);
            }

            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Inscription effectuée à la sortie ' . $sortie->getNom());
        } else {
            $this->addFlash('error', 'Désolé, les inscriptions sont maintenant clôturées pour la sortie ' . $sortie->getNom());
        }
        return $this->redirectToRoute('sortie_list');
    }

    #[Route('/sortie/{idSortie}/desinscription', name: 'sortie_unregister', methods: ["GET"])]
    public function unregister(int $idSortie,
                                SortieRepository $sortieRepository,
                                EtatRepository $etatRepository,
                                EntityManagerInterface $entityManager
    ): Response {
        $sortie = $sortieRepository->find($idSortie);
        $sortie->removeParticipant($this->getUser());

        if (count($sortie->getParticipants()) < $sortie->getNbInscriptionsMax()) {
            $etatOuverte = $etatRepository->findOneBy(['libelle' => 'Ouverte']);
            $sortie->setEtat($etatOuverte);
        }

        $entityManager->persist($sortie);
        $entityManager->flush();

        $this->addFlash('success', 'Vous n\'êtes plus inscrit à la sortie ' . $sortie->getNom());
        return$this->redirectToRoute('sortie_list');
    }

    #[Route('/sortie/display/{id}', name: 'sortie_display', methods: ["GET"])]
    public function displaySortie(int $id, SortieRepository $sortieRepository) : Response
    {
        $sortie = $sortieRepository->findOneBy(array('id' => $id));
        return $this->render('sortie/displaysortie.html.twig', [
            'sortie'=>$sortie
        ]);
    }

    #[Route('/sortie/{id}/annuler', name: 'sortie_cancel', methods: ["GET"])]
    public function cancelSortie(int $id, SortieRepository $sortieRepository, SortieStateUpdater $sortieStateUpdater) : Response {
        $sortie = $sortieRepository->find($id);

        if ($sortieStateUpdater->cancel($sortie)) {
            $this->addFlash('success', 'Cette sortie a été annulée');
        } else {
            $this->addFlash('error', 'Seules les sorties publiées et pas encore débutées peuvent être annulées');
        }

        return $this->redirectToRoute('sortie_update', ['id' => $id]);
    }
}
