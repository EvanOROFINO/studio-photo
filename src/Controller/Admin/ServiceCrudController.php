<?php

namespace App\Controller\Admin;

use App\Entity\Service;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ServiceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Service::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Prestation')
            ->setEntityLabelInPlural('Prestations')
            ->setDefaultSort(['position' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('title', 'Intitulé');
        yield TextareaField::new('description', 'Description')->hideOnIndex();
        yield MoneyField::new('priceFrom', 'À partir de')
            ->setCurrency('EUR')
            ->setStoredAsCents(false);
        yield TextField::new('duration', 'Durée')->hideOnIndex();
        yield TextField::new('icon', 'Icône Bootstrap (ex: bi-camera)')->hideOnIndex();
        yield IntegerField::new('position', 'Ordre');
        yield BooleanField::new('active', 'Actif');
    }
}
