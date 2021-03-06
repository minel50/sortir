<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pseudo', TextType::class, [
                'label' => 'Pseudo'
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom'
            ])

            ->add('nom', TextType::class, [
                'label' => 'Nom'
            ])

            ->add('telephone', TextType::class, [
                'label' => 'Téléphone'
            ])

            ->add('email', TextType::class, [
                'label' => 'Email'
            ])

            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'Nom'
            ])

            ->add('photo', FileType::class, [
                'mapped'=>false,
                'label' => 'Photo',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
