<?php

namespace App\Form;

use App\Entity\Course;
use App\Entity\Exam;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File as FileConstraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ExamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Exam Title',
                'required' => false,
                'attr' => ['placeholder' => 'Ex: Midterm, Final Exam...']
            ])
            ->add('date', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Exam Date and Time',
                'required' => false,
            ])
            ->add('duration', IntegerType::class, [
                'label' => 'Duration (minutes)',
                'required' => false,
                'attr' => ['placeholder' => 'Ex: 90']
            ])
            ->add('grade', NumberType::class, [
                'label' => 'Grade (/20)',
                'required' => false,
                'scale' => 2,
                'attr' => ['placeholder' => '15.5']
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
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'required' => false,
                'choices' => [
                    'Upcoming' => 'upcoming',
                    'Completed' => 'completed',
                    'Postponed' => 'postponed',
                ],
                'placeholder' => 'Select status',
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $exam = $event->getData();
            $form = $event->getForm();

            $isRequired = !$exam || null === $exam->getId() || null === $exam->getFile();

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
                    'message' => 'Please upload an exam file',
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
                'label' => 'External Link (Meet, Zoom, Resource)',
                'required' => false,
                'attr' => ['placeholder' => 'https://...']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Exam::class,
        ]);
    }
}
