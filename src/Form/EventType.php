<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'attr' => ['placeholder' => 'Enter event title', 'class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Title is required']),
                    new Assert\Length(['min' => 3, 'max' => 255]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'attr' => ['rows' => 4, 'class' => 'form-control', 'placeholder' => 'Event description...'],
                'constraints' => [new Assert\Length(['max' => 1000])],
            ])
            ->add('type', TextType::class, [
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'e.g., Exam, Project'],
                'constraints' => [new Assert\Length(['max' => 255])],
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['class' => 'form-control', 'type' => 'date'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Date is required']),
                    new Assert\GreaterThanOrEqual(['value' => new \DateTime('today'), 'message' => 'Date must be today or in the future']),
                ],
            ])
            ->add('duration', IntegerType::class, [
                'required' => false,
                'attr' => ['class' => 'form-control', 'min' => 1, 'max' => 1440],
                'constraints' => [
                    new Assert\GreaterThan(['value' => 0, 'message' => 'Duration must be greater than 0']),
                    new Assert\LessThanOrEqual(['value' => 1440, 'message' => 'Duration cannot exceed 1440 minutes']),
                ],
            ])
            ->add('location', TextType::class, [
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'e.g., Room 101, Online'],
                'constraints' => [new Assert\Length(['max' => 255])],
            ])
            ->add('status', ChoiceType::class, [
                'choices' => ['Pending' => 'Pending', 'In Progress' => 'In Progress', 'Completed' => 'Completed'],
                'placeholder' => 'Select a status',
                'attr' => ['class' => 'form-control'],
                'constraints' => [new Assert\NotBlank(), new Assert\Choice(['choices' => ['Pending', 'In Progress', 'Completed']])],
            ])
            ->add('priority', ChoiceType::class, [
                'choices' => ['High' => 'High', 'Medium' => 'Medium', 'Low' => 'Low'],
                'placeholder' => 'Select a priority',
                'attr' => ['class' => 'form-control'],
                'constraints' => [new Assert\NotBlank(), new Assert\Choice(['choices' => ['High', 'Medium', 'Low']])],
            ])
            ->add('difficulty', IntegerType::class, [
                'required' => false,
                'attr' => ['class' => 'form-control', 'min' => 1, 'max' => 5],
                'constraints' => [
                    new Assert\GreaterThanOrEqual(['value' => 1, 'message' => 'Difficulty must be at least 1']),
                    new Assert\LessThanOrEqual(['value' => 5, 'message' => 'Difficulty cannot exceed 5']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Event::class]);
    }
}
