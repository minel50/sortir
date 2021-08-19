<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;


use App\Entity\Ville;
use App\Repository\LieuRepository;
use App\Repository\VilleRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la sortie',
            ])

            ->add('dateHeureDebut', DateTimeType::class, ['label' => 'Date et heure de la sortie',

                    'date_widget' => 'single_text',
                    'html5' => true,
                    'view_timezone' => 'Europe/Paris'
            ])

            ->add('duree', IntegerType::class, [
                'label' => 'Durée'
            ])

            ->add('dateLimiteInscription', DateType::class, ['label' => 'Date limite inscription',
                'widget' => 'single_text',
                'html5' => true,
            ])

            ->add('nbInscriptionsMax', IntegerType::class, [
                'label' => 'Nombre de places',
                ])

            ->add('infosSortie', TextareaType::class, [
                'label' => 'Description et infos'])

            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => 'nom',
                'placeholder' => ''
            ])

            ->add('ville', EntityType::class, [
                'mapped' => false,
                'class' => Ville::class,
                'choice_label' => function ($ville) {
                    return $ville->getCodePostal().' '.$ville->getNom();
                },
                'query_builder' => function (VilleRepository $villeRepository) {
                    return $villeRepository->createQueryBuilder('v')
                                ->orderBy('v.codePostal, v.nom', 'ASC');
                },
                'placeholder' => ''
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class
        ]);
    }
}
