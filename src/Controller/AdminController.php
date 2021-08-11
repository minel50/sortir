<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantCreateType;
use App\Form\ProfilType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_home', methods: ['GET'])]
    public function displayAdminHomePage(): Response {
        return $this->render('admin/home.html.twig');
    }

    #[Route('/admin/creer-participant', name: 'admin_createParticipant', methods: ['GET', 'POST'])]
    public function createParticipant(Request $request,
                                        UserPasswordEncoderInterface $passwordEncoder,
                                        EntityManagerInterface $entityManager
    ): Response {
        $participant = new Participant();
        $participant->setPassword($passwordEncoder->encodePassword($participant, 'eni123'));
        $participant->setTelephone('0241000000');
        $participant->setAdmin(false);
        $participant->setActif(true);
        $participantForm = $this->createForm(ParticipantCreateType::class, $participant);
        $participantForm->handleRequest($request);

        if ($participantForm->isSubmitted() && $participantForm->isValid()) {

            $entityManager->persist($participant);
            $entityManager->flush();

            $this->addFlash('success', $participant->getPrenom() . ' ' . $participant->getNom() . ' a été créé');
            return $this->redirectToRoute('admin_home');
        }

        return $this->render('admin/create-participant.html.twig', [
            'participantForm' => $participantForm->createView()
        ]);
    }
}
