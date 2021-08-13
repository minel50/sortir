<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Repository\LieuRepository;
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

class UpdateSortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $latitude = $options['latitude'];

        $builder
            ->add('nom', TextType::class, ['label' => false, 'attr' => [
                'placeholder' => 'Nom de la sortie'
            ]])
            ->add('dateHeureDebut', DateTimeType::class, ['label' => 'Date et heure de la sortie',

                'date_widget' => 'single_text',
                'html5' => true,

            ])
            ->add('duree', IntegerType::class
                , ['label' => false, 'attr' => [
                    'placeholder' => 'Durée de la sortie'
                ]])
            ->add('dateLimiteInscription', DateType::class, ['label' => 'Date limite inscription',
                'widget' => 'single_text',
                'html5' => true,

            ])
            ->add('nbInscriptionsMax', IntegerType::class, ['label' => false, 'attr' => [
                'placeholder' => 'Nb max participants'
            ]])
            ->add('infosSortie', TextareaType::class, ['label' => false, 'attr' => [
                'placeholder' => 'Description et infos'
            ]])
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => 'nom',
                "query_builder"=> function(LieuRepository $lieuRepository) use($options) {
                    return $lieuRepository->getLatitude($options['latitude']);
                },


            ])



            ->add('latitude', NumberType::class,
                ['label' => false,
                    'mapped'=>false,

                    'attr' => [
                    'placeholder' => 'Latitude',

                ],
                    ])
            ->add('longitude', NumberType::class,
                ['label' => false,
                    'mapped'=>false,

                    'attr' => [
                    'placeholder' => 'Longitude',

                ],
                ])




            ->add('campus', EntityType::class,[
                'class'=>Campus::class,
                'choice_label'=>'nom'
            ])


        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
            'latitude'=>null,


        ]);
    }
}
