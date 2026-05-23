<?php

namespace App\Form;

use App\Entity\ContactRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName', TextType::class, [
                'label' => 'Nom complet',
                'attr' => ['placeholder' => 'Marie Dupont', 'class' => 'form-control'],
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['placeholder' => 'marie@exemple.fr', 'class' => 'form-control'],
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('phone', TelType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'attr' => ['placeholder' => '06 12 34 56 78', 'class' => 'form-control'],
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('projectType', ChoiceType::class, [
                'label' => 'Type de projet',
                'choices' => ContactRequest::TYPES,
                'placeholder' => '— Choisissez —',
                'attr' => ['class' => 'form-select'],
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('eventDate', DateType::class, [
                'label' => 'Date de l\'événement (si connue)',
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Votre message',
                'attr' => [
                    'placeholder' => 'Décrivez-moi votre projet, le lieu, vos envies…',
                    'rows' => 6,
                    'class' => 'form-control',
                ],
                'label_attr' => ['class' => 'form-label'],
            ])
            // Honeypot anti-spam — left blank by humans, filled by bots
            ->add('website', TextType::class, [
                'mapped' => false,
                'required' => false,
                'label' => false,
                'attr' => ['tabindex' => -1, 'autocomplete' => 'off', 'aria-hidden' => 'true'],
                'row_attr' => ['style' => 'position:absolute; left:-9999px; height:0; overflow:hidden;'],
            ])
            ->add('rendered_at', HiddenType::class, [
                'mapped' => false,
                'data' => (string) time(),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactRequest::class,
        ]);
    }
}
