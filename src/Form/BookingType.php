<?php

namespace App\Form;

use App\Entity\Booking;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('clientName', TextType::class, [
                'label' => 'Nom complet',
                'attr' => ['placeholder' => 'Marie Dupont', 'class' => 'form-control'],
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('clientEmail', EmailType::class, [
                'label' => 'Email',
                'attr' => ['placeholder' => 'marie@exemple.fr', 'class' => 'form-control'],
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('clientPhone', TelType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'attr' => ['placeholder' => '06 12 34 56 78', 'class' => 'form-control'],
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('eventDate', DateType::class, [
                'label' => 'Date souhaitée',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('location', TextType::class, [
                'label' => 'Lieu (ville, lieu-dit, salle…)',
                'required' => false,
                'attr' => ['placeholder' => 'Lyon, Château de Loulou…', 'class' => 'form-control'],
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Précisions sur votre projet',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Nombre d\'invités, ambiance souhaitée, contraintes particulières…',
                    'rows' => 4,
                    'class' => 'form-control',
                ],
                'label_attr' => ['class' => 'form-label'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Booking::class,
        ]);
    }
}
