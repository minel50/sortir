<?php

namespace App\Controller;



use App\Entity\Ville;
use App\Form\UpdateVilleType;
use App\Form\VilleType;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VilleController extends AbstractController
{
    #[Route('/admin/ville/liste', name: 'ville_liste')]
    public function liste(VilleRepository $villeRepository,EntityManagerInterface $entityManager,Request $request): Response
    {
        $ville = new Ville();
        $villeListe= $villeRepository->findAll();
        $villeForm = $this->createForm(VilleType::class,$ville);
        $villeForm->handleRequest($request);

        if (  $villeForm->isSubmitted() && $villeForm->isValid() ){

            $villeListe = $villeRepository->getByNom($villeForm->get('search')->getData());

        }
        if( $villeForm->get('ajouter')->isClicked() && $villeForm->isSubmitted() && $villeForm->isValid() )  {

            $entityManager->persist($ville);
            $entityManager -> flush();
            return $this->redirect($this->generateUrl('ville_liste'));


        }



        return $this->render('ville/liste.html.twig', [
            'controller_name' => 'VilleController',
            'ville'=>$ville,
            'villeListe'=>$villeListe,
            'villeForm'=>$villeForm->createView()
        ]);
    }
    #[Route('/admin/ville/update/{id}', name: 'ville_update')]
    public function update(VilleRepository $villeRepository,int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $ville = $villeRepository->find($id);
        $upDateForm = $this->createForm(UpdateVilleType::class,$ville);
        $upDateForm->handleRequest($request);

        if ( $upDateForm->get('modifier')->isClicked()  && $upDateForm->isSubmitted() && $upDateForm->isValid() ) {

            $entityManager->persist($ville);
            $entityManager->flush();
            return $this->redirect($this->generateUrl('ville_liste'));


        }
        if ( $upDateForm->get('supprimer')->isClicked()  && $upDateForm->isSubmitted() && $upDateForm->isValid() ) {
            $entityManager->remove($ville);
            $entityManager->flush();
            return $this->redirect($this->generateUrl('ville_liste'));

        }






        return $this->render('ville/update.html.twig', [
            'controller_name' => 'VilleController',
            'ville'=>$ville,
            'upDateForm'=>$upDateForm->createView(),

        ]);
    }


}
