<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LieuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du lieu : ',
                'mapped' => false
            ])

            ->add('rue', TextType::class, [
                'label' => 'Adresse : ',
                'mapped' => false
            ])

            ->add('latitude',TextType::class, [
                'label' => 'Latitude : ',
                'mapped' => false,
            ])

            ->add('longitude',TextType::class, [
                'label' => 'Longitude : ',
                'mapped' => false,
            ])

            ->add('ville', TextType::class, [
                'label' => 'Ville : ',
                'mapped' => false
            ])

            ->add('cp', TextType::class, [
                'label' => 'Code postal : ',
                'mapped' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
