<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ProfilType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class ParticipantController extends AbstractController
{
    /**
     * @Route("/participant", name="participant_gestionProfil")
     */
    public function gestionProfil(Request $request, EntityManagerInterface $entityManager, UserInterface $user)
    {
        $participant = $user;

        $participantForm = $this->createForm(ProfilType::class, $participant);

        $participantForm->handleRequest($request);

        if($participantForm->isSubmitted() && $participantForm->isValid())
        {
            $entityManager->persist($participant);
            $entityManager->flush();
        }

        return $this->render('participant/profil.html.twig', [
            'participantForm' => $participantForm->createView()
        ]);
    }


}