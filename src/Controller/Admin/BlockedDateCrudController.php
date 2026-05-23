<?php

namespace App\Controller\Admin;

use App\Entity\BlockedDate;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class BlockedDateCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return BlockedDate::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Date bloquée')
            ->setEntityLabelInPlural('Dates bloquées')
            ->setDefaultSort(['startDate' => 'ASC'])
            ->setHelp('index', 'Marquez ici les périodes où vous ne souhaitez pas recevoir de réservation : congés, formation, week-ends off, etc.');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield DateField::new('startDate', 'Du')->setFormTypeOption('input', 'datetime_immutable');
        yield DateField::new('endDate', 'Au (inclus, optionnel)')
            ->setFormTypeOption('input', 'datetime_immutable')
            ->setRequired(false)
            ->setHelp('Laisser vide pour une seule journée.');
        yield TextField::new('reason', 'Raison')
            ->setHelp('Vacances, formation, repos…');
        yield DateTimeField::new('createdAt', 'Ajoutée le')->hideOnForm();
    }
}
