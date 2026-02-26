<?php

namespace App\Form;

use App\Entity\Group;
use App\Entity\Project;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('status', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
                'choices' => [
                    'En attente' => 'PENDING',
                    'En cours' => 'IN_PROGRESS',
                    'Terminé' => 'COMPLETED',
                ],
            ])
            ->add('resource', \Symfony\Component\Form\Extension\Core\Type\FileType::class, [
                'label' => 'Ressources (Fichier)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                            'image/*',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader un document valide (PDF, Image, Word)',
                    ])
                ],
            ])
            ->add('deadline', \Symfony\Component\Form\Extension\Core\Type\DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('type', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
                'choices' => [
                    'Recherche' => 'RESEARCH',
                    'Développement' => 'DEVELOPMENT',
                    'Design' => 'DESIGN',
                    'Autre' => 'OTHER',
                ],
            ])
            ->add('group', EntityType::class, [
                'class' => Group::class,
                'choice_label' => 'category',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
