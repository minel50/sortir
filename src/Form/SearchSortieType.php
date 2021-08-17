<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchSortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
            $builder
                ->add('campus',EntityType::class,[
                    'class' => Campus::class,
                    'choice_label' => 'Nom',
                    'required'=>false,
                    'data' => $options['campus'],
                    'placeholder' => 'Tous',
                    'mapped' => false,
                ])
               ->add('nom',TextType::class,[
                    'label'=>false,
                    'mapped'=>false,
                    'required'=>false,
                    'attr'=>[
                        'class'=>'form-control',
                        'placeholder'=>'Entrez un nom'
                    ]
                ])
                ->add('from',DateType::class,   [
                    'widget' => 'single_text',
                    'html5'=>true,
                    'mapped'=>false,
                    'required'=>false,
                    'label' => 'Entre'
                ])
                ->add('to',DateType::class,[
                    'widget' => 'single_text',
                    'html5'=>true,
                    'mapped'=>false,
                    'required'=>false,
                    'label' => 'et'
                ])
                ->add('isOrganisateur', CheckboxType::class, [
                    'label' => 'Sorties dont je suis l\'organisateur/trice',
                    'required' => false
                ])
                ->add('isInscrit', CheckboxType::class, [
                    'label' => 'Sorties auxquelles je suis inscrit/e',
                    'required' => false
                ])
                ->add('isNotInscrit', CheckboxType::class, [
                    'label' => 'Sorties auxquelles je ne suis pas inscrit/e',
                    'required' => false
                ])
                ->add('isDone', CheckboxType::class, [
                    'label' => 'Sorties passées',
                    'required' => false
                ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            //'data_class' => Sortie::class,
            'data_class' => null,
            'campus' => null
        ]);
    }

    private function getUser()
    {
    }
}
