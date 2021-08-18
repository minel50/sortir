<?php

namespace App\Controller;

use App\Entity\Campus;

use App\Form\CampusType;


use App\Form\UpdateCampusType;
use App\Repository\CampusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CampusController extends AbstractController
{
    #[Route('/admin/campus/liste', name: 'campus_liste')]
    public function liste(CampusRepository $campusRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $campus = new Campus();
        $campusListe= $campusRepository->findAll();
        $campusForm = $this->createForm(CampusType::class,$campus);
        $campusForm->handleRequest($request);

        if ( $campusForm->isSubmitted() && $campusForm->isValid() ){

             $campusListe = $campusRepository->getByNom($campusForm->get('search')->getData());

        }
       if( $campusForm->get('ajouter')->isClicked() && $campusForm->isSubmitted() && $campusForm->isValid() )  {

           $entityManager->persist($campus);
           $entityManager -> flush();
            return $this->redirect($this->generateUrl('campus_liste'));


       }



        return $this->render('campus/liste.html.twig', [
            'controller_name' => 'CampusController',
            'campus' => $campus,
            'campusListe'=>$campusListe,
            'campusForm' => $campusForm->createView(),
        ]);
    }
    #[Route('/admin/campus/update/{id}', name: 'campus_update')]
    public function update(CampusRepository $campusRepository,int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $campus = $campusRepository->find($id);
        $updateForm = $this->createForm(UpdateCampusType::class,$campus);
        $updateForm->handleRequest($request);

        if ( $updateForm->get('modifier')->isClicked()  && $updateForm->isSubmitted() && $updateForm->isValid() ) {

            $entityManager->persist($campus);
            $entityManager->flush();
            return $this->redirect($this->generateUrl('campus_liste'));


        }
        if ( $updateForm->get('supprimer')->isClicked()  && $updateForm->isSubmitted() && $updateForm->isValid() ) {
            $entityManager->remove($campus);
            $entityManager->flush();
            return $this->redirect($this->generateUrl('campus_liste'));

        }






            return $this->render('campus/update.html.twig', [
                'controller_name' => 'CampusController',
                'campus'=>$campus,
                'updateForm'=>$updateForm->createView(),

        ]);
    }








}
