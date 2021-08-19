<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\MdpType;
use App\Form\ProfilType;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ParticipantController extends AbstractController
{
    /**
     * @Route("/participant", name="participant_gestionProfil")
     */
    public function gestionProfil(Request $request, EntityManagerInterface $entityManager)
    {
        //Equivalent de l'attribut UserInterface -> compris dans le AbstractController
        $participant = $this->getUser();

        $participantForm = $this->createForm(ProfilType::class, $participant);

        $participantForm->handleRequest($request);

        if($participantForm->isSubmitted() && $participantForm->isValid())
        {
            if(!$participantForm['photo']->isEmpty())
            {
                //traitement images
                $uploads_directory=$this->getParameter('photos_directory'); //dans config/services.yaml
                $filePhoto = $participantForm['photo']->getData();

                $fileName=md5(uniqid()).'.'.$filePhoto->guessExtension();
                $filePhoto->move(
                    $uploads_directory,
                    $fileName
                );

                //Si il y a déjà une photo associée au participant on la supprime
                if($participant->getPhoto()){
                    $oldFileName = $participant->getPhoto();
                    $filesystem = new Filesystem();
                    $filesystem->remove($uploads_directory.'/'.$oldFileName);
                }

                //Associer nom de la nouvelle photo
                $participant->setPhoto($fileName);

            }

            //MAJ BDD
            $entityManager->persist($participant);
            $entityManager->flush();

            $this->addFlash('success', 'Modifications effectuées sur votre profil');
        }

        return $this->render('participant/profil.html.twig', [
            'participantForm' => $participantForm->createView(),
            'participant' => $participant
        ]);
    }


    /**
     * @Route("/participant/mdp", name="participant_gestionMDP")
     */
    public function gestionMDP(Request $request, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $encoder)
    {

        $participant = $this->getUser();
        $participantForm = $this->createForm(MdpType::class, $participant);
        $participantForm->handleRequest($request);

        if($participantForm->isSubmitted() && $participantForm->isValid())
        {
            $mdp = $participantForm["password"]->getData();
            $confirmmdp = $participantForm["confirmpassword"]->getData();
            $oldmdp = $participantForm["oldpassword"]->getData();

            //Encoder la valeur du champ de l'ancien password valid
            $validOldMdp = $encoder->isPasswordValid(
                $this->getUser(),
                $oldmdp
            );

            //Check si ancien password valid
            if($mdp == $confirmmdp)
            {
                if($validOldMdp)
                {
                    //encoder le nouveau mdp enregistré
                    $participant->setPassword(
                        $encoder->encodePassword(
                            $participant,
                            $mdp
                        )
                    );

                    //MAJ BDD
                    $entityManager->persist($participant);
                    $entityManager->flush();
                    $this->addFlash('success', 'Le mot de passe a été modifié.');

                }else{
                    $this->addFlash("error", "Mot de passe actuel incorrect.");
                }
            }else{
                $this->addFlash("error", "La confirmation du mot de passe est obligatoire et doit être valide.");
            }
            return $this->redirectToRoute('participant_gestionProfil');
        }

        return$this->render('participant/profilmdp.html.twig', [
            'participantForm' => $participantForm->createView(),
            'participant' => $participant
        ]);
    }

    /**
     * @Route("participant/afficher/{id}", name="participant_display")
     */
    public function liste(int $id, ParticipantRepository $participantRepository)
    {
        $participant = $participantRepository->findOneBy(array('id' => $id));

        return $this->render('participant/display.html.twig', [
            'id' => $id,
            'participant' => $participant
        ]);
    }

    /**
     * @Route("admin/participant/afficher", name="participant_all")
     */
    public function listeAll(ParticipantRepository $participantRepository)
    {
        $participants = $participantRepository->findAll();


        return $this->render('admin/listeparticipants.html.twig', [
            'participants' => $participants
        ]);
    }

    /**
     * @Route("admin/participant/desactiver/{id}", name="participant_desactiver")
     */
    public function desactiverParticipant(int $id,
                                          ParticipantRepository $participantRepository,
                                          EntityManagerInterface $entityManager
    )
    {
        $participant = $participantRepository->findOneBy(array('id' => $id));

        $participant->setActif(false);

        $entityManager->persist($participant);
        $entityManager->flush();

        return $this->redirectToRoute('participant_all');
    }

    /**
     * @Route("admin/participant/activer/{id}", name="participant_activer")
     */
    public function activerParticipant(int $id,
                                          ParticipantRepository $participantRepository,
                                          EntityManagerInterface $entityManager
    )
    {
        $participant = $participantRepository->findOneBy(array('id' => $id));

        $participant->setActif(true);

        $entityManager->persist($participant);
        $entityManager->flush();

        return $this->redirectToRoute('participant_all');
    }

    /**
     * @Route("admin/participant/supprimer/{id}", name="participant_supprimer")
     */
    public function supprimerParticipant(Participant $participant, SortieRepository $sortieRepository)
    {
        //Si l'utilisateur avait uploadé une photo on la supprime
        if($participant->getPhoto()){
            $uploads_directory=$this->getParameter('photos_directory'); //dans config/services.yaml
            $filePhoto = $participant->getPhoto();
            $filesystem = new Filesystem();
            $filesystem->remove($uploads_directory.'/'.$filePhoto);
        }

        $em = $this->getDoctrine()->getManager();

        //Désinscrit l'utilisateur de toutes les sorties où il participe
        $listeSortiesParticipees = $participant->getSortiesParticipees();
        foreach ($listeSortiesParticipees as $s){
            $s->removeParticipant($participant);
            //$this->addFlash('warning', "Utilisateur désinscrit.");
        }

        //Supprimer les sorties que l'utilisateur a organisé
        $listeSortiesOrganisees = $sortieRepository->findBy(array('organisateur' => $participant));
        foreach ($listeSortiesOrganisees as $s){
            $em->remove($s);
            //$this->addFlash('warning', "Sortie supprimée.");
        }

        //Supprimer l'utilisateur'
        $em->remove($participant);

        $em->flush();
        $this->addFlash('warning', "L'utilisateur a été supprimé.");
        return $this->redirectToRoute('participant_all');
    }

}