<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\SearchDateType;
use App\Form\SearchSortieType;
use App\Form\SortieType;
use App\Form\UpdateSortieType;
use App\Repository\CampusRepository;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use App\Service\SortieStateUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints\Date;

class SortieController extends AbstractController
{
    #[Route('/sortie/creer', name: 'sortie_create')]
    public function create(Request $request, EntityManagerInterface $entityManager,EtatRepository $etatRepository,VilleRepository $villeRepository,SortieRepository $sortieRepository): Response
    {
        //rediriger vers la page principale si user désactivé
        if($this->getUser()->getActif() == 0){
            return $this->redirectToRoute('sortie_list');
        }

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
                            SortieStateUpdater $sortieStateUpdater,
                            SessionInterface $session,
                            CampusRepository $campusRepository
    ): Response
    {
        //if loading the page : default values or user's choice if exists in session
        $nom = $session->get('nom', null);
        $campusId = $session->get('campusId', -1);

        if ($campusId == 0) {   //user's choice is all campus
            $campus = null;
        } else if ($campusId > 0) {     //user's choice is one campus
            $campus = $campusRepository->find($campusId);
        } else if ($campusId == -1) {    //since he is connected, user has not yet submit the form
            $campus = $this->getUser()->getCampus();
        }

        $from = $session->get('from', null);
        $to = $session->get('to', null);
        $isOrganisateur = $session->get('isOrganisateur', true);
        $isInscrit = $session->get('isInscrit', true);
        $isNotInscrit = $session->get('isNotInscrit', true);
        $isDone = $session->get('isDone', false);


        $listeForm = $this->createForm(SearchSortieType::class, null, [
            'campus' => $campus
        ]);
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

            //save in session user's choice
            $session->set('nom', $nom);

            if ($campus != null) {
                $session->set('campusId', $campus->getId());
            } else {
                $session->set('campusId', 0);
            }

            $session->set('from', $from);
            $session->set('to', $to);
            $session->set('isOrganisateur', $isOrganisateur);
            $session->set('isInscrit', $isInscrit);
            $session->set('isNotInscrit', $isNotInscrit);
            $session->set('isDone', $isDone);

        }

        $participant = $this->getUser();
        $sorties=$sortieRepository->getByCampus($nom,$campus,$from,$to, $isOrganisateur, $isInscrit, $isNotInscrit,
            $isDone, $participant);

        //Service to update state ('Ouverte' <-> 'Clôturée' -> 'Activité en cours' -> 'Passée').
        //Switch between 'Ouverte' and 'Clôturée', depending of participants number and registering date.
        //Switch to 'Activité en cours' at the beginning datetime.
        //Switch to 'Passée' at the beginning datetime + duration.
        $sortieStateUpdater->updateState($sorties);

        return $this->render('sortie/list.html.twig', [
            'controller_name' => 'SortieController',
            'sorties'=>$sorties,
            'listeForm'=>$listeForm->createView(),
            'isOrganisateur' => $isOrganisateur,
            'isInscrit' => $isInscrit,
            'isNotInscrit' => $isNotInscrit,
            'isDone' => $isDone
        ]);
    }


    #[Route('/sortie/update/{id}', name: 'sortie_update')]
    public function update(Request $request,SortieRepository $sortieRepository, int $id,EntityManagerInterface $entityManager,LieuRepository $lieuRepository): Response
    {

        //rediriger vers la page principale si user désactivé
        if($this->getUser()->getActif() == 0){
            return $this->redirectToRoute('sortie_list');
        }

        $sortie=$sortieRepository->find($id);

        //Only events with state "Créée" can be modified
        if ($sortie->getEtat()->getLibelle() != "Créée") {
            $this->addFlash('error', 'Cette sortie ne peut plus être modifiée');
            return $this->redirectToRoute('sortie_list');
        }

        //Only organisator can modify an event
        if ($sortie->getOrganisateur() != $this->getUser()) {
            $this->addFlash('error', 'Vous ne pouvez pas modifier cette sortie car vous n\'êtes pas l\'organisateur');
            return $this->redirectToRoute('sortie_list');
        }

        $updateForm = $this->createForm(UpdateSortieType::class,$sortie);
        $updateForm->handleRequest($request);

        if ($updateForm->isSubmitted() && $updateForm->isValid()) {

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

        //rediriger vers la page principale si user désactivé
        if($this->getUser()->getActif() == 0){
            return $this->redirectToRoute('sortie_list');
        }

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

    #[Route('/sortie/desinscription/{idSortie}', name: 'sortie_unregister', methods: ["GET"])]
    public function unregister(int $idSortie,
                                SortieRepository $sortieRepository,
                                EtatRepository $etatRepository,
                                EntityManagerInterface $entityManager
    ): Response {

        //rediriger vers la page principale si user désactivé
        if($this->getUser()->getActif() == 0){
            return $this->redirectToRoute('sortie_list');
        }

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

    #[Route('/sortie/afficher/{id}', name: 'sortie_display', methods: ["GET"])]
    public function displaySortie(int $id, SortieRepository $sortieRepository) : Response
    {
        $sortie = $sortieRepository->findOneBy(array('id' => $id));

        //Events can be displayed only one month after their beginning datetime
        $now = new \DateTime();
        if ($sortie->getDateHeureDebut()->modify('+1 month') < $now ) {
            $this->addFlash('error', 'Cette sortie n\'est plus visible car elle date de plus d\'un mois');
            return $this->redirectToRoute('sortie_list');
        }

        return $this->render('sortie/displaysortie.html.twig', [
            'sortie'=>$sortie
        ]);
    }

    #[Route('/sortie/annuler/{id}', name: 'sortie_cancel', methods: ["GET", "POST"])]
    public function cancelSortie(int $id,
                                 SortieRepository $sortieRepository,
                                 SortieStateUpdater $sortieStateUpdater,
                                 Request $request
    ) : Response {

        //rediriger vers la page principale si user désactivé
        if($this->getUser()->getActif() == 0){
            return $this->redirectToRoute('sortie_list');
        }

        $sortie = $sortieRepository->find($id);
        $cancelForm = $this->createForm('App\Form\CancelSortieType');
        $cancelForm->handleRequest($request);

        if ($cancelForm->isSubmitted() && $cancelForm->isValid()) {
            $cancelReason = $cancelForm['cancel']->getData();
            $sortie->setInfosSortie(
                $sortie->getInfosSortie() . ' Sortie annulée pour le motif suivant : ' . $cancelReason
            );

            if ($sortieStateUpdater->cancel($sortie)) {
                $this->addFlash('success', 'Cette sortie a été annulée');
            } else {
                $this->addFlash('error', 'Cette sortie ne peut pas être annulée');
            }

            return $this->redirectToRoute('sortie_update', ['id' => $id]);
        }

        return $this->render('sortie/cancelSortie.html.twig', [
            'cancelForm' => $cancelForm->createView(),
            'sortie' => $sortie
        ]);
    }

    #[Route('/sortie/publier/{id}', name: 'sortie_publish', methods: ["GET"])]
    public function publishSortie(int $id,
                                    SortieRepository $sortieRepository,
                                    SortieStateUpdater $sortieStateUpdater
    ) : Response {

        //rediriger vers la page principale si user désactivé
        if($this->getUser()->getActif() == 0){
            return $this->redirectToRoute('sortie_list');
        }

        $sortie = $sortieRepository->find($id);

        if ($sortieStateUpdater->publish($sortie)) {
            $this->addFlash('success', 'La sortie ' . $sortie->getNom() . ' a été publiée');
        } else {
            $this->addFlash('error', 'La sortie n\'a pas pu être publiée');
            $this->addFlash('error', 'Seul l\'organisateur de la sortie peut réaliser cette opération sur une sortie créée');
        }

        return $this->redirectToRoute('sortie_list');
    }

    #[Route('/sortie/supprimer/{id}', name: 'sortie_delete', methods: ["GET"])]
    public function deleteSortie(int $id,
                                SortieRepository $sortieRepository,
                                EntityManagerInterface $entityManager
    ) : Response {

        //rediriger vers la page principale si user désactivé
        if($this->getUser()->getActif() == 0){
            return $this->redirectToRoute('sortie_list');
        }

        $sortie = $sortieRepository->find($id);

        if ($sortie->getOrganisateur() === $this->getUser() && $sortie->getEtat()->getLibelle() === 'Créée') {
            $entityManager->remove($sortie);
            $entityManager->flush();
            $this->addFlash('success', 'La sortie a été supprimée');
        } else {
            $this->addFlash('error', 'La sortie n\'a pas pu être supprimée');
            return $this->redirectToRoute('sortie_update', ['id' => $id]);
        }

        return $this->redirectToRoute('sortie_list');
    }

    #[Route('/admin/sortie/list', name: 'admin_sortie_list')]
    public function adminListSortie(SortieRepository $sortieRepository) : Response
    {

        $sorties =  $sortieRepository->findAll();

        return $this->render('admin/listsorties.html.twig', [
            'sorties' => $sorties
        ]);
    }

    #[Route('/admin/sortie/list/{id}', name: 'admin_sortie_cancel')]
    public function adminCancelSortie(int $id,
                                        EtatRepository $etatRepository,
                                        SortieRepository $sortieRepository,
                                        EntityManagerInterface $em
    ) : Response
    {
        $etatAnnule = $etatRepository->findOneBy(array('id' => 6));
        $sortie =  $sortieRepository->findOneBy(array('id' => $id));

        //$dateActuelle =  date('H:i:s \O\n d/m/Y') > ;

        if($sortie->getEtat()->getId() == 1 or $sortie->getEtat()->getId() == 2){
            $sortie->setEtat($etatAnnule);
        }

        $em->persist($sortie);
        $em->flush();

        return $this->redirectToRoute('admin_sortie_list');
    }
}
