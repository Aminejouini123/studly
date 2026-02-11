<?php

namespace App\Form;

use App\Entity\Course;
use App\Entity\Exam;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File as FileConstraint;

class ExamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de l\'examen',
                'attr' => ['placeholder' => 'Ex: Partiel, Examen Final...']
            ])
            ->add('date', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date et Heure de l\'examen',
            ])
            ->add('duration', IntegerType::class, [
                'label' => 'Durée (minutes)',
                'attr' => ['placeholder' => 'Ex: 90']
            ])
            ->add('grade', NumberType::class, [
                'label' => 'Note (/20)',
                'required' => false,
                'scale' => 2,
                'attr' => ['placeholder' => '15.5']
            ])
            ->add('difficulty', ChoiceType::class, [
                'label' => 'Difficulté',
                'choices' => [
                    'Facile' => 'Facile',
                    'Moyen' => 'Moyen',
                    'Difficile' => 'Difficile',
                ],
                'placeholder' => 'Choisir la difficulté',
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'À venir' => 'à venir',
                    'Terminé' => 'terminé',
                    'Reporté' => 'reporté',
                ],
                'placeholder' => 'Choisir le statut',
            ])
            ->add('file', FileType::class, [
                'label' => 'Fichier joint (PDF, Docx)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new FileConstraint([
                        'maxSize' => '10M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'image/jpeg',
                            'image/png'
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un document valide (PDF, Word, Image)',
                    ])
                ],
            ])
            ->add('link', UrlType::class, [
                'label' => 'Lien externe (Meet, Zoom, Ressource)',
                'required' => false,
                'attr' => ['placeholder' => 'https://...']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Exam::class,
        ]);
    }
}
