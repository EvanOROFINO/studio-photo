<?php

namespace App\Form;

use App\Entity\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customerName', TextType::class, [
                'label' => 'Nom complet',
                'attr' => ['placeholder' => 'Marie Dupont', 'class' => 'form-control'],
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('customerEmail', EmailType::class, [
                'label' => 'Email',
                'attr' => ['placeholder' => 'marie@exemple.fr', 'class' => 'form-control'],
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('customerPhone', TelType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'attr' => ['placeholder' => '06 12 34 56 78', 'class' => 'form-control'],
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('shippingAddress', TextareaType::class, [
                'label' => 'Adresse de livraison',
                'attr' => ['placeholder' => '12 rue de la Liberté, appt 3', 'rows' => 2, 'class' => 'form-control'],
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('shippingZip', TextType::class, [
                'label' => 'Code postal',
                'attr' => ['placeholder' => '69001', 'class' => 'form-control'],
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('shippingCity', TextType::class, [
                'label' => 'Ville',
                'attr' => ['placeholder' => 'Lyon', 'class' => 'form-control'],
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('shippingCountry', TextType::class, [
                'label' => 'Pays',
                'attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'form-label'],
                'data' => 'France',
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes / instructions (optionnel)',
                'required' => false,
                'attr' => ['rows' => 3, 'class' => 'form-control'],
                'label_attr' => ['class' => 'form-label'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
