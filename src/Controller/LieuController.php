<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuType;
use App\Repository\LieuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LieuController extends AbstractController
{
    #[Route('/lieu/creer', name: 'lieu_create')]
    public function create(Request $request,
                            EntityManagerInterface $entityManager,
                            LieuRepository $lieuRepository
    ):Response {
        $lieu = new Lieu();
        $lieuForm = $this->createForm(LieuType::class, $lieu);
        $lieuForm->handleRequest($request);

        if ($lieuForm->isSubmitted() && $lieuForm->isValid()) {
            //check if lieu name already used in database
            if ($lieuRepository->findOneByName($lieu->getNom())) {
                $this->addFlash('error', 'Ce nom est déjà utilisé par un autre lieu');
            } else {
                $entityManager->persist($lieu);
                $entityManager->flush();

                $this->addFlash('success', 'Le lieu ' . $lieu->getNom() . ' a été créé avec succès');

                return $this->redirectToRoute('sortie_create');
            }
        }

        return $this->render('lieu/create.html.twig', [
            'lieuForm' => $lieuForm->createView()
        ]);
    }
}
