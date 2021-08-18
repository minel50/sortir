<?php

namespace App\Form;

use App\Entity\Campus;

use App\Repository\SortieRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CampusType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom',TextType::class,['label'=>false,'required'=>false])

            ->add('search',SearchType::class,[
                'label'=>false,
                'mapped'=>false,
                'required'=>false,
                'attr'=>[
                    'class'=>'form-control',
                    'placeholder'=>'Entrez un nom'
                ] ])
            ->add('ajouter',SubmitType::class,['label'=>'ajouter',
                'attr'=>[
                    'class'=>'btn btn-light'
                ]])
            ->add('rechercher',SubmitType::class,['label'=>'Rechercher',
                'attr'=>[
                    'class'=>'btn btn-light'
                ]
                ])





        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Campus::class,
        ]);
    }
}
