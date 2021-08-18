<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Repository\SortieRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class SearchSortieType extends AbstractType
{

    private $security;
    public function __construct(Security $security){
        $this->security= $security;
    }


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
                    'label'=> 'Le nom de la sortie contient',
                    'mapped'=>false,
                    'required'=>false,
                    'attr'=>[
                        'class'=>'form-control',
                        'placeholder'=>'Rechercher'
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
                    'required' => false,
                    'label_attr' => [
                        'class' => 'app-form-label-cb'
                    ]
                ])
                ->add('isInscrit', CheckboxType::class, [
                    'label' => 'Sorties auxquelles je suis inscrit/e',
                    'required' => false,
                    'label_attr' => [
                        'class' => 'app-form-label-cb'
                    ]
                ])
                ->add('isNotInscrit', CheckboxType::class, [
                    'label' => 'Sorties auxquelles je ne suis pas inscrit/e',
                    'required' => false,
                    'label_attr' => [
                        'class' => 'app-form-label-cb'
                    ]
                ])
                ->add('isDone', CheckboxType::class, [
                    'label' => 'Sorties passées',
                    'required' => false,
                    'label_attr' => [
                        'class' => 'app-form-label-cb'
                    ]
                ])
            ;
    }







    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            //'data_class' => Sortie::class,
            'data_class' => null,
            'campus'=>null





        ]);
    }


}
