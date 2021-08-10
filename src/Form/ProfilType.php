<?php

namespace App\Form;

use App\Entity\Participant;
use Symfony\Component\Form\AbstractType;
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
                'label' => 'Téléphone'
            ])
            //->add('password')
            //->add('roles')
            //->add('admin')
            //->add('actif')
            ->add('campus', TextType::class, [
                'label' => 'Téléphone'
            ])
            ->add('photo', TextType::class, [
                'label' => 'Téléphone',
                'required' => 'false'
            ])
            //->add('sortiesParticipees')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
