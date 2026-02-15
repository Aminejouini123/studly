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
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ActivityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Activity Title',
                'required' => false,
                'attr' => ['placeholder' => 'Ex: Lab 1, Homework...']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 4, 'placeholder' => 'Activity details...']
            ])
            ->add('duration', IntegerType::class, [
                'label' => 'Estimated Duration (minutes)',
                'required' => false,
                'attr' => ['placeholder' => 'Ex: 60']
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'required' => false,
                'choices' => [
                    'To Do' => 'to do',
                    'In Progress' => 'in progress',
                    'Completed' => 'completed',
                ],
                'placeholder' => 'Select status',
            ])
            ->add('difficulty', ChoiceType::class, [
                'label' => 'Difficulty',
                'required' => false,
                'choices' => [
                    'Easy' => 'Easy',
                    'Medium' => 'Medium',
                    'Hard' => 'Hard',
                ],
                'placeholder' => 'Select difficulty',
            ])
            ->add('level', ChoiceType::class, [
                'label' => 'Required Level',
                'required' => false,
                'choices' => [
                    'Beginner' => 'Beginner',
                    'Intermediate' => 'Intermediate',
                    'Advanced' => 'Advanced',
                ],
                'placeholder' => 'Select level',
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $activity = $event->getData();
            $form = $event->getForm();

            $isRequired = !$activity || null === $activity->getId() || null === $activity->getFile();

            $constraints = [
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
            ];

            if ($isRequired) {
                $constraints[] = new NotBlank([
                    'message' => 'Please upload an activity file',
                ]);
            }

            $form->add('file', FileType::class, [
                'label' => 'Attached File (PDF, Docx)',
                'mapped' => false,
                'required' => false,
                'constraints' => $constraints,
            ]);
        });

        $builder
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
