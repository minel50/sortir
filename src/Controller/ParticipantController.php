<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ParticipantController extends AbstractController
{
    /**
     * @Route("/gestionProfil", name="participant_gestionProfil")
     */
    public function gestionProfil()
    {
        return $this->render('participant/profil.html.twig');
    }
}