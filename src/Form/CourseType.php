<?php

namespace App\Form;

use App\Entity\Course;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CourseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('courseFile')
            ->add('courseLink')
            ->add('teacherEmail')
            ->add('semester')
            ->add('difficultyLevel')
            ->add('type')
            ->add('priority')
            ->add('coefficient')
            ->add('status')
            ->add('duration')
            ->add('comment')
            ->add('createdAt')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Course::class,
        ]);
    }
}
