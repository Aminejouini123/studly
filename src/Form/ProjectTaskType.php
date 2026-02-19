<?php

namespace App\Form;

use App\Entity\Project;
use App\Entity\ProjectTask;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectTaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'To Do' => ProjectTask::STATUS_TO_DO,
                    'In Progress' => ProjectTask::STATUS_IN_PROGRESS,
                    'Done' => ProjectTask::STATUS_DONE,
                ],
            ])
            ->add('deadline', DateTimeType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('assignedUser', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getFirstName() . ' ' . $user->getLastName();
                },
                'placeholder' => 'Assign to...',
                'required' => false,
            ])
            ->add('project', EntityType::class, [
                'class' => Project::class,
                'choice_label' => 'title',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProjectTask::class,
        ]);
    }
}
