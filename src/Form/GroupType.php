<?php

namespace App\Form;

use App\Entity\Group;
use App\Entity\MemberGroup;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Validator\Constraints as Assert;

class GroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('capacity', IntegerType::class, [
                'required' => true,
                'attr' => ['min' => 1],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Positive(),
                ],
            ])
            ->add('groupPhoto', null, [
                'required' => true,
                'attr' => ['placeholder' => 'https://...'],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 5]),
                ],
            ])
            ->add('category', null, [
                'required' => true,
                'attr' => ['placeholder' => 'e.g. Mathematics Study Group'],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 2, 'max' => 255]),
                ],
            ])
            ->add('memberGroup', EntityType::class, [
                'class' => MemberGroup::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Group::class,
        ]);
    }
}
