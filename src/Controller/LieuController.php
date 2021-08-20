<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Ville;
use App\Form\LieuType;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class LieuController extends AbstractController
{
    #[Route('/lieu/creer', name: 'lieu_create')]
    public function create(Request $request,
                            EntityManagerInterface $entityManager,
                            VilleRepository $villeRepository,
                            LieuRepository $lieuRepository
    ):Response {

        //rediriger vers la page principale si user désactivé
        if($this->getUser()->getActif() == 0){
            return $this->redirectToRoute('sortie_list');
        }

        $lieuForm = $this->createForm(LieuType::class, null);
        $lieuForm->handleRequest($request);

        if ($lieuForm->isSubmitted() && $lieuForm->isValid()) {

            //save field values in variables
            $nom = $lieuForm["nom"]->getData();
            $rue = $lieuForm["rue"]->getData();
            $latitude = $lieuForm["latitude"]->getData();
            $longitude = $lieuForm["longitude"]->getData();
            $nomVille = $lieuForm["ville"]->getData();
            $cp = $lieuForm["cp"]->getData();

            $ville = new Ville();
            $ville->setNom($nomVille);
            $ville->setCodePostal($cp);

            $lieu = new Lieu();
            $lieu->setNom($nom);
            $lieu->setRue($rue);
            $lieu->setLatitude($latitude);
            $lieu->setLongitude($longitude);


            //check si ville existe dans BDD
            if (!$villeRepository->findOneBy([
                'nom' => $nomVille,
                'codePostal' => $cp
            ])) {
                $entityManager->persist($ville);
                $entityManager->flush();
            }

            //check si lieu existe dans BDD
            if ($lieuRepository->findOneBy([
                'nom' => $nom,
                'rue' => $rue
            ])) {
                $this->addFlash('error', 'Ce lieu est déjà enregistré');
            } else {

                //récupérer ville dans BDD
                $ville = $villeRepository->findOneBy([
                    'nom' => $nomVille,
                    'codePostal' => $cp
                ]);

                //lier la ville au lieu
                $lieu->setVille($ville);

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

    #[Route('/lieu/lieux-par-ville', name: 'lieu_filter', methods: ['GET'])]
    public function filter(Request $request,
                            VilleRepository $villeRepository,
                            LieuRepository $lieuRepository,
                            SerializerInterface $serializer
    ) {
        //get parameter idVille of javascript fetch request
        $idVille = $request->get('idVille');

        if ($idVille != null) {
            $ville = $villeRepository->find($idVille);
            $lieux = $lieuRepository->findBy(['ville' => $ville]);

            $json = $serializer->serialize($lieux, 'json', ['groups' => "liste_lieux"]);

            return new JsonResponse($json, Response::HTTP_OK, [], true);
        }

        return new Response("Erreur");
    }

    #[Route('/lieu/lieu-sortie/{idSortie}', name: 'lieu_get', methods: ['GET'])]
    public function getLieuBySortie(Request $request,
                           SortieRepository $sortieRepository,
                           SerializerInterface $serializer,
                            int $idSortie
    ) {
        if ($idSortie != null) {
            $sortie = $sortieRepository->find($idSortie);
            $lieu = $sortie->getLieu();

            $json = $serializer->serialize($lieu, 'json', ['groups' => "liste_lieux"]);

            return new JsonResponse($json, Response::HTTP_OK, [], true);
        }

        return new Response("Erreur");
    }
}
