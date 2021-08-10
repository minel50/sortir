<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom',TextType::class,['label'=>false,'attr'=>[
                'placeholder'=>'Nom de la sortie'
            ]])
            ->add('dateHeureDebut',DateTimeType::class,['label'=>'Date et heure de la sortie',
            'attr'=>[
                'date_widget'=>'single_text',
                'html5'=>true,
    ]
        ])


            ->add('duree',IntegerType::class
                ,['label'=>false,'attr'=>[
                    'placeholder'=>'Durée de la sortie'
                ]])
            ->add('dateLimiteInscription',DateType::class,['label'=>'Date limite inscription'])
            ->add('nbInscriptionsMax',IntegerType::class,['label '=>false,'attr'=>[
                'placeholder'=>'Nombre max participants'
            ]])
            ->add('infosSortie',TextareaType::class,['label'=>false,'attr'=>[
                'placeholder'=>'Description et infos'
            ]])
            ->add('lieu', EntityType::class,[
                'class'=>Lieu::class,
                'choice_label'=>'nom'
            ])
            /*->add('campus', EntityType::class,[
                'class'=>Campus::class,
                'choice_label'=>'nom'
            ])
            ->add('etat', EntityType::class,[
                'class'=>Etat::class,
                'choice_label'=>'libelle'
            ])
            ->add('organisateur', EntityType::class,[
                'class'=>Particant::class,
                'choice_label'=>'organisateur-id'
            ])
            ->add('participants', EntityType::class,[
                'class'=>Participant::class,
                'choice_label'=>'participant-id'
            ])*/
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
