<?php

namespace App\Form;

use App\Entity\Ville;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateVilleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom')
            ->add('codePostal')
            ->add('modifier',SubmitType::class,['label'=>'modifier',
                'attr'=>[
                    'class'=>'btn btn-light'
                ]])
            ->add('supprimer',SubmitType::class,['label'=>'supprimer',
                'attr'=>[
                    'class'=>'btn btn-light'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Ville::class,
        ]);
    }
}
