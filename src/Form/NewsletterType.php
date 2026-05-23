<?php

namespace App\Form;

use App\Entity\NewsletterSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewsletterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('email', EmailType::class, [
            'label' => false,
            'attr' => [
                'placeholder' => 'Votre email',
                'class' => 'form-control',
                'aria-label' => 'Email pour la newsletter',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => NewsletterSubscriber::class,
            'csrf_protection' => true,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
