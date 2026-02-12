<?php

namespace App\Form;

use App\Entity\Course;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File as FileConstraint;

class CourseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Course Name',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Ex: Object Oriented Programming',
                    'class' => 'form-control'
                ],
            ])
            ->add('teacherEmail', EmailType::class, [
                'label' => 'Teacher Email',
                'required' => true,
                'attr' => [
                    'placeholder' => 'prof@example.com',
                    'class' => 'form-control'
                ],
            ])
            ->add('semester', ChoiceType::class, [
                'label' => 'Semester',
                'required' => true,
                'choices' => [
                    'S1' => 'S1',
                    'S2' => 'S2',
                    'S3' => 'S3',
                   
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('difficultyLevel', ChoiceType::class, [
                'label' => 'Difficulty Level',
                'required' => true,
                'choices' => [
                    'Easy' => 'Easy',
                    'Medium' => 'Medium',
                    'Hard' => 'Hard',
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Course Type',
                'required' => true,
                'choices' => [
                    'Lecture' => 'Lecture',
                    'Practical' => 'Practical',
                    'Mixed' => 'Mixed',
                    'Online' => 'Online',
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('priority', ChoiceType::class, [
                'label' => 'Priority',
                'required' => true,
                'choices' => [
                    'Low' => 'Low',
                    'Normal' => 'Normal',
                    'High' => 'High',
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'required' => true,
                'choices' => [
                    'Pending' => 'Pending',
                    'In Progress' => 'In Progress',
                    'Completed' => 'Completed',
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('coefficient', NumberType::class, [
                'label' => 'Coefficient',
                'required' => true,
                'attr' => [
                    'placeholder' => '1.5',
                    'step' => '0.5',
                    'class' => 'form-control'
                ],
            ])
            ->add('duration', IntegerType::class, [
                'label' => 'Duration (hours)',
                'required' => true,
                'attr' => [
                    'placeholder' => '30',
                    'min' => '1',
                    'class' => 'form-control'
                ],
            ])
            ->add('courseFile', FileType::class, [
                'label' => 'Course File (PDF, DOCX, ...)',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new FileConstraint([
                        'maxSize' => '10M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/zip',
                            'image/png',
                            'image/jpeg'
                        ],
                        'mimeTypesMessage' => 'Please upload a valid file (PDF/DOCX/PNG/JPEG).',
                    ])
                ],
            ])
            ->add('courseLink', UrlType::class, [
                'label' => 'Course Link',
                'required' => false,
                'attr' => [
                    'placeholder' => 'https://example.com/course',
                    'class' => 'form-control'
                ],
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Comments',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Add notes or comments...',
                    'rows' => 4,
                    'class' => 'form-control'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Course::class,
        ]);
    }
}
