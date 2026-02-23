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
        $group = $options['group'];

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
                'query_builder' => function (\Doctrine\ORM\EntityRepository $er) use ($group) {
                    return $er->createQueryBuilder('u')
                        ->join('u.groups', 'g')
                        ->where('g.id = :group_id')
                        ->orWhere('u.id = :creator_id')
                        ->setParameter('group_id', $group ? $group->getId() : 0)
                        ->setParameter('creator_id', $group ? $group->getCreator()->getId() : 0)
                        ->distinct();
                },
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
            'group' => null,
        ]);
    }
}
