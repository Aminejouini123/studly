<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class)
            // Use the JSON roles field
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Student' => 'ROLE_ETUDIANT',
                    'Admin' => 'ROLE_ADMIN',
                ],
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'attr' => ['autocomplete' => 'new-password'],
                'required' => false, // Allow empty for edit
            ])
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('dateOfBirth', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('phoneNumber', TextType::class)
            ->add('address', TextareaType::class)
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'Active' => 'Active',
                    'Inactive' => 'Inactive',
                    'Pending' => 'Pending'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();
                if ($data && is_null($data->getId())) {
                    return ['Default', 'create'];
                }
                return ['Default'];
            },
        ]);
    }
}
