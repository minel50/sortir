<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    #[Route('/sortie/create', name: 'sortie_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sortie = new Sortie();
        $sortieForm = $this->createForm(SortieType::class,$sortie);
        $sortieForm->handleRequest($request);
        if($sortieForm->isSubmitted() && $sortieForm->isValid())
        {
            $entityManager->persist($sortie);
            $entityManager->flush();
        }




        return $this->render('sortie/create.html.twig', [
            'controller_name' => 'SortieController',
            'sortie'=>$sortie,
            'sortieForm'=>$sortieForm->createView()


        ]);
    }
    #[Route('/sortie/list', name: 'sortie_list')]
    public function list(SortieRepository $sortieRepository): Response
    {    //$sorties = $sortieRepository->findSorties();
        $sorties = $sortieRepository->findAll();
        dump($sorties);
        return $this->render('sortie/list.html.twig', [
            'controller_name' => 'SortieController',
            'sorties'=>$sorties,

        ]);

    }

    #[Route('/sortie/{idSortie}/inscription', name: 'sortie_register', methods: ["GET"])]
    public function register(int $idSortie,
                            SortieRepository $sortieRepository,
                            EntityManagerInterface $entityManager
    ): Response {
        $sortie = $sortieRepository->find($idSortie);
        $sortie->addParticipant($this->getUser());

        $entityManager->persist($sortie);
        $entityManager->flush();

        $this->addFlash('success', 'Inscription effectuée à la sortie ' . $sortie->getNom());
        return $this->redirectToRoute('sortie_list');
    }

    #[Route('/sortie/{idSortie}/desinscription', name: 'sortie_unregister', methods: ["GET"])]
    public function unregister(int $idSortie,
                                SortieRepository $sortieRepository,
                                EntityManagerInterface $entityManager
    ): Response {
        $sortie = $sortieRepository->find($idSortie);
        $sortie->removeParticipant($this->getUser());

        $entityManager->persist($sortie);
        $entityManager->flush();

        $this->addFlash('success', 'Vous n\'êtes plus inscrit à la sortie ' . $sortie->getNom());
        return$this->redirectToRoute('sortie_list');
    }
}
