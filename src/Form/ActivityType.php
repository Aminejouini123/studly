<?php

namespace App\Form;

use App\Entity\Activity;
use App\Entity\Course;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File as FileConstraint;

class ActivityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Activity Title',
                'attr' => ['placeholder' => 'Ex: Lab 1, Homework...']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['rows' => 4, 'placeholder' => 'Activity details...']
            ])
            ->add('duration', IntegerType::class, [
                'label' => 'Estimated Duration (minutes)',
                'attr' => ['placeholder' => 'Ex: 60']
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'choices' => [
                    'To Do' => 'to do',
                    'In Progress' => 'in progress',
                    'Completed' => 'completed',
                ],
                'placeholder' => 'Select status',
            ])
            ->add('difficulty', ChoiceType::class, [
                'label' => 'Difficulty',
                'choices' => [
                    'Easy' => 'Easy',
                    'Medium' => 'Medium',
                    'Hard' => 'Hard',
                ],
                'placeholder' => 'Select difficulty',
            ])
            ->add('level', ChoiceType::class, [
                'label' => 'Required Level',
                'choices' => [
                    'Beginner' => 'Beginner',
                    'Intermediate' => 'Intermediate',
                    'Advanced' => 'Advanced',
                ],
                'placeholder' => 'Select level',
            ])
            ->add('file', FileType::class, [
                'label' => 'Attached File (PDF, Docx)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new FileConstraint([
                        'maxSize' => '10M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'image/jpeg',
                            'image/png'
                        ],
                        'mimeTypesMessage' => 'Please upload a valid document (PDF, Word, Image)',
                    ])
                ],
            ])
            ->add('link', UrlType::class, [
                'label' => 'External Link (Resource)',
                'required' => false,
                'attr' => ['placeholder' => 'https://...']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Activity::class,
        ]);
    }
}
