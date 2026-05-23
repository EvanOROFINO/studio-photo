<?php

namespace App\Controller\Admin;

use App\Entity\ContactRequest;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ContactRequestCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ContactRequest::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Demande de contact')
            ->setEntityLabelInPlural('Demandes de contact')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->disable(Action::NEW);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('fullName', 'Nom');
        yield TextField::new('email');
        yield TextField::new('phone', 'Téléphone')->hideOnIndex();
        yield ChoiceField::new('projectType', 'Type de projet')
            ->setChoices(ContactRequest::TYPES);
        yield DateField::new('eventDate', 'Date événement')->hideOnIndex();
        yield TextareaField::new('message')->hideOnIndex();
        yield ChoiceField::new('status', 'Statut')
            ->setChoices([
                'Nouveau' => ContactRequest::STATUS_NEW,
                'Lu' => ContactRequest::STATUS_READ,
                'Traité' => ContactRequest::STATUS_REPLIED,
            ]);
        yield DateTimeField::new('createdAt', 'Reçue le')->hideOnForm();
    }
}
