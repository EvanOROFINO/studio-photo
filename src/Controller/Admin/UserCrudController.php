<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield EmailField::new('email');
        yield TextField::new('fullName', 'Nom complet');
        yield ChoiceField::new('roles')
            ->setChoices([
                'Admin' => 'ROLE_ADMIN',
                'Utilisateur' => 'ROLE_USER',
            ])
            ->allowMultipleChoices()
            ->renderAsBadges();

        $passwordOptions = [
            'type' => PasswordType::class,
            'first_options' => ['label' => 'Mot de passe'],
            'second_options' => ['label' => 'Confirmer'],
            'mapped' => false,
            'required' => $pageName === Crud::PAGE_NEW,
        ];

        yield TextField::new('plainPassword', 'Mot de passe')
            ->setFormType(RepeatedType::class)
            ->setFormTypeOptions($passwordOptions)
            ->onlyOnForms();
    }

    public function createEntity(string $entityFqcn): User
    {
        return new User();
    }

    public function updateEntity(\Doctrine\ORM\EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->hashPlainPassword($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    public function persistEntity(\Doctrine\ORM\EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->hashPlainPassword($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    private function hashPlainPassword(User $user): void
    {
        $form = $this->getContext()?->getRequest()->request->all('User');
        if (!empty($form['plainPassword']['first'])) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $form['plainPassword']['first']));
        }
    }
}
