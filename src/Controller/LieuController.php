<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuType;
use App\Repository\LieuRepository;
use App\Repository\VilleRepository;
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
                            VilleRepository $villeRepository,
                            LieuRepository $lieuRepository
    ):Response {
        $lieu = new Lieu();
        $lieuForm = $this->createForm(LieuType::class, $lieu);
        $lieuForm->handleRequest($request);

        if ($lieuForm->isSubmitted() && $lieuForm->isValid()) {
            //save field values in variables
            $nom = $lieuForm["nom"]->getData();
            $rue = $lieuForm["rue"]->getData();
            $latitude = $lieuForm["latitude"]->getData();
            $longitude = $lieuForm["longitude"]->getData();
            $ville = $lieuForm["ville"]->getData();
            $cp = $lieuForm["cp"]->getData();


            dd($nom, $rue, $latitude, $longitude, $ville, $cp);
            //check if ville already used in database
            if ($checktest) {
                $this->addFlash('error', 'Cette ville est déjà enregistrée');
            }

            //check if lieu name already used in database
//            if ($lieuRepository->findOneByName($lieu->getNom())) {
//                $this->addFlash('error', 'Ce nom est déjà utilisé par un autre lieu');
//            } else {
//                $entityManager->persist($lieu);
//                $entityManager->flush();
//
//                $this->addFlash('success', 'Le lieu ' . $lieu->getNom() . ' a été créé avec succès');
//
//                return $this->redirectToRoute('sortie_create');
//            }
        }

        return $this->render('lieu/create.html.twig', [
            'lieuForm' => $lieuForm->createView()
        ]);
    }
}
