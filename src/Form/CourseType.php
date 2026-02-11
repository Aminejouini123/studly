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

class CourseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du Cours',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Ex: Programmation Orientée Objet',
                    'class' => 'form-control'
                ],
            ])
            ->add('teacherEmail', EmailType::class, [
                'label' => 'Email du Professeur',
                'required' => true,
                'attr' => [
                    'placeholder' => 'prof@exemple.com',
                    'class' => 'form-control'
                ],
            ])
            ->add('semester', ChoiceType::class, [
                'label' => 'Semestre',
                'required' => true,
                'choices' => [
                    'S1' => 'S1',
                    'S2' => 'S2',
                    'S3' => 'S3',
                   
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('difficultyLevel', ChoiceType::class, [
                'label' => 'Niveau de Difficulté',
                'required' => true,
                'choices' => [
                    'Facile' => 'Facile',
                    'Moyen' => 'Moyen',
                    'Difficile' => 'Difficile',
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type de Cours',
                'required' => true,
                'choices' => [
                    'Magistral' => 'Magistral',
                    'Pratique' => 'Pratique',
                    'Mixte' => 'Mixte',
                    'En ligne' => 'En ligne',
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('priority', ChoiceType::class, [
                'label' => 'Priorité',
                'required' => true,
                'choices' => [
                    'Basse' => 'Basse',
                    'Normale' => 'Normale',
                    'Élevée' => 'Élevée',
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'required' => true,
                'choices' => [
                    'En attente' => 'En attente',
                    'En cours' => 'En cours',
                    'Terminé' => 'Terminé',
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('coefficient', NumberType::class, [
                'label' => 'Coefficient',
                'required' => true,
                'attr' => [
                    'placeholder' => '1.5',
                    'step' => '0.5',
                    'class' => 'form-control'
                ],
            ])
            ->add('duration', IntegerType::class, [
                'label' => 'Durée (en heures)',
                'required' => true,
                'attr' => [
                    'placeholder' => '30',
                    'min' => '1',
                    'class' => 'form-control'
                ],
            ])
            ->add('courseFile', FileType::class, [
                'label' => 'Fichier du Cours (PDF, DOCX, ...)',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => [
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
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier valide (PDF/DOCX/PNG/JPEG).',
                    ])
                ],
            ])
            ->add('courseLink', UrlType::class, [
                'label' => 'Lien du Cours',
                'required' => false,
                'attr' => [
                    'placeholder' => 'https://exemple.com/cours',
                    'class' => 'form-control'
                ],
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Commentaires',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ajouter des notes ou des commentaires...',
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
