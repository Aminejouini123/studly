<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Motivation;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class MotivationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('motivationLevel', IntegerType::class, [
                'attr' => ['min' => 1, 'max' => 10, 'class' => 'form-control'],
                'constraints' => [
                    new Assert\Range(['min' => 1, 'max' => 10, 'notInRangeMessage' => 'Must be between 1 and 10']),
                ],
            ])
            ->add('emotion', ChoiceType::class, [
                'choices' => [
                    'Excited' => 'excited',
                    'Happy' => 'happy',
                    'Motivated' => 'motivated',
                    'Stressed' => 'stressed',
                    'Anxious' => 'anxious',
                    'Calm' => 'calm',
                    'Determined' => 'determined',
                    'Focused' => 'focused',
                ],
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please select an emotion']),
                ],
            ])
            ->add('preparation', TextType::class, [
                'attr' => ['placeholder' => 'e.g., reviewed notes, prepared materials', 'class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Preparation is required']),
                    new Assert\Length(['min' => 3, 'max' => 255, 'minMessage' => 'Min 3 chars', 'maxMessage' => 'Max 255 chars']),
                ],
            ])
            ->add('reward', TextType::class, [
                'attr' => ['placeholder' => 'e.g., movie, ice cream, break', 'class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Reward is required']),
                    new Assert\Length(['min' => 3, 'max' => 255, 'minMessage' => 'Min 3 chars', 'maxMessage' => 'Max 255 chars']),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Motivation::class,
            // User requested to remove CSRF check ("fix it delete it")
            'csrf_protection' => false,
        ]);
    }
}
