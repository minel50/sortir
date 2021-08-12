<?php

namespace App\Form;

use App\Entity\Participant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MdpType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder

            ->add('password', PasswordType::class, [
                'label' => 'Nouveau mot de passe : ',
                'mapped' => false
            ])

            ->add('oldpassword', PasswordType::class, [
                'label' => 'Mot de passe actuel : ',
                'mapped' => false
            ])

            ->add('confirmpassword', PasswordType::class, [
                'label' => 'Confirmez le nouveau mot de passe : ',
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
