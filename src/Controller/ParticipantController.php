<?php

namespace App\Controller;

use App\Entity\Participant;
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
        $participantForm = $this->createForm();

        return $this->render('participant/profil.html.twig');
    }
}