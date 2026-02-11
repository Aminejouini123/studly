<?php

namespace App\Form;

use App\Entity\Activity;
use App\Entity\Course;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActivityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de l\'activité',
                'attr' => ['placeholder' => 'Ex: TP1, Devoir Maison...']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['rows' => 4, 'placeholder' => 'Détails de l\'activité...']
            ])
            ->add('duration', IntegerType::class, [
                'label' => 'Durée estimée (minutes)',
                'attr' => ['placeholder' => 'Ex: 60']
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'À faire' => 'à faire',
                    'En cours' => 'en cours',
                    'Terminé' => 'terminé',
                ],
                'placeholder' => 'Choisir le statut',
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
            ->add('level', ChoiceType::class, [
                'label' => 'Niveau requis',
                'choices' => [
                    'Débutant' => 'Débutant',
                    'Intermédiaire' => 'Intermédiaire',
                    'Avancé' => 'Avancé',
                ],
                'placeholder' => 'Choisir le niveau',
            ])
            ->add('file', FileType::class, [
                'label' => 'Fichier joint (PDF, Docx)',
                'mapped' => false,
                'required' => false,
            ])
            ->add('link', UrlType::class, [
                'label' => 'Lien externe (Ressource)',
                'required' => false,
                'attr' => ['placeholder' => 'https://...']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Activity::class,
        ]);
    }
}
