<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\MdpType;
use App\Form\ProfilType;
use App\Repository\ParticipantRepository;
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

                //modif nom d'image + supprimer image si existante
                $oldFileName = $participant->getPhoto();
                $participant->setPhoto($fileName);
                $filesystem = new Filesystem();
                $filesystem->remove($uploads_directory.'/'.$oldFileName);
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
     * @Route("/participant/password", name="participant_gestionMDP")
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

            //Check si ancien password valid
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
                    return $this->redirectToRoute('participant_gestionProfil');

                }else{
                    $this->addFlash("error", "Mot de passe actuel incorrect.");
                    return $this->redirectToRoute('participant_gestionProfil');
                }
            }else{
                $this->addFlash("error", "La confirmation du mot de passe est obligatoire et doit être valide.");
                return $this->redirectToRoute('participant_gestionProfil');
            }

        }

        return$this->render('participant/profilmdp.html.twig', [
            'participantForm' => $participantForm->createView(),
            'participant' => $participant
        ]);
    }

    /**
     * @Route("participant/display/{id}", name="participant_display")
     */
    public function liste(int $id, ParticipantRepository $participantRepository)
    {
        $participant = $participantRepository->findOneBy(array('id' => $id));

        return $this->render('participant/display.html.twig', [
            'id' => $id,
            'participant' => $participant
        ]);
    }

}