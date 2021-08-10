<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ProfilType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ParticipantController extends AbstractController
{
    /**
     * @Route("/gestionProfil", name="participant_gestionProfil")
     */
    public function gestionProfil()
    {
        $participant = new Participant();
        $participantForm = $this->createForm(ProfilType::class, $participant);

        return $this->render('participant/profil.html.twig', [
            'participantForm' => $participantForm->createView()
        ]);
    }
}