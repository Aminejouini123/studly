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
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CourseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Course Name',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ex: Object Oriented Programming',
                    'class' => 'form-control'
                ],
            ])
            ->add('teacherEmail', EmailType::class, [
                'label' => 'Teacher Email',
                'required' => false,
                'attr' => [
                    'placeholder' => 'prof@example.com',
                    'class' => 'form-control'
                ],
            ])
            ->add('semester', ChoiceType::class, [
                'label' => 'Semester',
                'required' => false,
                'choices' => [
                    'S1' => 'S1',
                    'S2' => 'S2',
                    'S3' => 'S3',
                   
                ],
                'placeholder' => 'Select Semester',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('difficultyLevel', ChoiceType::class, [
                'label' => 'Difficulty Level',
                'required' => false,
                'choices' => [
                    'Easy' => 'Easy',
                    'Medium' => 'Medium',
                    'Hard' => 'Hard',
                ],
                'placeholder' => 'Select Difficulty',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Course Type',
                'required' => false,
                'choices' => [
                    'Lecture' => 'Lecture',
                    'Practical' => 'Practical',
                    'Mixed' => 'Mixed',
                    'Online' => 'Online',
                ],
                'placeholder' => 'Select Type',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('priority', ChoiceType::class, [
                'label' => 'Priority',
                'required' => false,
                'choices' => [
                    'Low' => 'Low',
                    'Normal' => 'Normal',
                    'High' => 'High',
                ],
                'placeholder' => 'Select Priority',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'required' => false,
                'choices' => [
                    'Pending' => 'Pending',
                    'In Progress' => 'In Progress',
                    'Completed' => 'Completed',
                ],
                'placeholder' => 'Select Status',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('coefficient', NumberType::class, [
                'label' => 'Coefficient',
                'required' => false,
                'attr' => [
                    'placeholder' => '1.5',
                    'step' => '0.5',
                    'class' => 'form-control'
                ],
            ])
            ->add('duration', IntegerType::class, [
                'label' => 'Duration (hours)',
                'required' => false,
                'attr' => [
                    'placeholder' => '30',
                    'min' => '1',
                    'class' => 'form-control'
                ],
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $course = $event->getData();
            $form = $event->getForm();

            // Check if we are creating a new course or if the course has no file yet
            $isRequired = !$course || null === $course->getId() || null === $course->getCourseFile();

            $constraints = [
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
            ];

            if ($isRequired) {
                $constraints[] = new NotBlank([
                    'message' => 'Please upload a course file',
                ]);
            }

            $form->add('courseFile', FileType::class, [
                'label' => 'Course File (PDF, DOCX, ...)',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control'
                ],
                // Add the NotBlank constraint ONLY if required
                'constraints' => $constraints,
            ]);
        });
        
        $builder
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
