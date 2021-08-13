<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\SearchDateType;
use App\Form\SearchSortieType;
use App\Form\SortieType;
use App\Form\UpdateSortieType;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class SortieController extends AbstractController
{
    #[Route('/sortie/create', name: 'sortie_create')]
    public function create(Request $request, EntityManagerInterface $entityManager,EtatRepository $etatRepository,VilleRepository $villeRepository,SortieRepository $sortieRepository): Response
    {
        $sortie = new Sortie();

       // $sortie = $sortieRepository->find(1);
        $ville = $villeRepository->find(1);


        $user = $this->getUser();
        $campus=$user->getCampus();
        $sortieForm = $this->createForm(SortieType::class,$sortie,[
            'ville'=>$ville
        ]);
        $sortieForm->handleRequest($request);
        if($sortieForm->isSubmitted() && $sortieForm->isValid())
        {

            $sortie->setOrganisateur($user);
            $sortie->setCampus($campus);
            $sortie->setEtat($etatRepository->findOneBy(['libelle'=>'Créée']));
            $entityManager->persist($sortie);
            $entityManager->flush();


            return $this->redirectToRoute('sortie_list');

        }



        return $this->render('sortie/create.html.twig', [
            'controller_name' => 'SortieController',
            'sortie'=>$sortie,
            'sortieForm'=>$sortieForm->createView(),



        ]);
    }
    #[Route('/sortie/list', name: 'sortie_list')]
    public function list(SortieRepository $sortieRepository, Request $request): Response
    {    //$sorties = $sortieRepository->findSorties();

        $sorties = $sortieRepository->findAll();

        $listeForm = $this->createForm(SearchSortieType::class);
        $listeForm->handleRequest($request);
        if($listeForm->isSubmitted() && $listeForm->isValid()) {

                $sorties=$sortieRepository->search($listeForm->get('mots')->getData());
        }

        $dateForm = $this->createForm(SearchDateType::class);
        $dateForm->handleRequest($request);
        if($dateForm->isSubmitted() && $dateForm->isValid()) {
            $from = $dateForm['from']->getData();
            $to = $dateForm['to']->getData();
            $sorties = $sortieRepository->searchSortieByDate($from,$to);

        }

        return $this->render('sortie/list.html.twig', [
            'controller_name' => 'SortieController',
            'sorties'=>$sorties,
            //'sortie'=>$sortie,
            'listeForm'=>$listeForm->createView(),
            'dateForm'=>$dateForm->createView()


        ]);

    }


    #[Route('/sortie/update/{id}', name: 'sortie_update')]
    public function update(Request $request,SortieRepository $sortieRepository, int $id,EntityManagerInterface $entityManager,LieuRepository $lieuRepository): Response
    {


        $latitude = $lieuRepository->find(1);
        $sortie=$sortieRepository->find($id);
        $updateForm = $this->createForm(UpdateSortieType::class,$sortie,['latitude'=>$latitude]);

        $updateForm->handleRequest($request);
        if($updateForm->isSubmitted() && $updateForm->isValid()) {

            $entityManager->persist($sortie);
            $entityManager->flush();


            return $this->redirectToRoute('sortie_list');

        }


        return $this->render('sortie/update.html.twig', [
            'controller_name' => 'SortieController',
            'sortie'=>$sortie,
            'updateForm'=>$updateForm->createView()


        ]);


    }


}
