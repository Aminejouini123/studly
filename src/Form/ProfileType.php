<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('profilePicture', FileType::class, [
                'label' => 'Profile Picture',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image (JPEG, PNG, WEBP)',
                    ])
                ],
                'attr' => ['class' => 'file-input', 'accept' => 'image/*']
            ])
            ->add('firstName', TextType::class, [
                'label' => 'First Name'
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Last Name'
            ])
            ->add('email', TextType::class, [
                'label' => 'Email',
                'disabled' => true, // Email usually cannot be changed easily or needs verification
                'help' => 'Contact support to change email.'
            ])
            ->add('bio', TextareaType::class, [
                'required' => false,
                'label' => 'Bio',
                'attr' => ['rows' => 4, 'placeholder' => 'Tell us about yourself...']
            ])
            ->add('jobTitle', TextType::class, [
                'required' => false,
                'label' => 'Job Title / Career'
            ])
            ->add('educationLevel', TextType::class, [
                'required' => false,
                'label' => 'Education Level'
            ])
            ->add('website', TextType::class, [
                'required' => false,
                'label' => 'Website / Portfolio',
                'attr' => ['placeholder' => 'https://...']
            ])
            ->add('phoneNumber', TextType::class, [
                'required' => false,
                'label' => 'Phone Number'
            ])
            ->add('address', TextType::class, [
                'required' => false,
                'label' => 'Address'
            ])
            ->add('dateOfBirth', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'label' => 'Date of Birth'
            ])
            ->add('skills', TextType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Skills (comma separated)',
                'data' => $builder->getData() && $builder->getData()->getSkills() ? implode(', ', $builder->getData()->getSkills()) : '',
                'attr' => ['placeholder' => 'Python, Cloud, Marketing...']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
