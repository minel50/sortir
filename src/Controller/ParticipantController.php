<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ProfilType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
        }

        return $this->render('participant/profil.html.twig', [
            'participantForm' => $participantForm->createView(),
            'participant' => $participant
        ]);
    }


}