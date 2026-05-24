<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class OrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Commande')
            ->setEntityLabelInPlural('Boutique — Commandes')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->disable(Action::NEW);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('reference', 'Réf.');
        yield TextField::new('customerName', 'Client');
        yield TextField::new('customerEmail', 'Email')->hideOnIndex();
        yield TextField::new('customerPhone', 'Téléphone')->hideOnIndex();
        yield TextareaField::new('shippingAddress', 'Adresse')->hideOnIndex();
        yield TextField::new('shippingZip', 'CP')->hideOnIndex();
        yield TextField::new('shippingCity', 'Ville')->hideOnIndex();
        yield TextField::new('shippingCountry', 'Pays')->hideOnIndex();
        yield IntegerField::new('totalQuantity', 'Articles')->onlyOnIndex();
        yield MoneyField::new('totalAmount', 'Total')->setCurrency('EUR')->setStoredAsCents(false);
        yield ChoiceField::new('status', 'Statut')
            ->setChoices(Order::STATUSES)
            ->renderAsBadges([
                Order::STATUS_PENDING => 'warning',
                Order::STATUS_PAID => 'info',
                Order::STATUS_SHIPPED => 'primary',
                Order::STATUS_DELIVERED => 'success',
                Order::STATUS_CANCELLED => 'secondary',
                Order::STATUS_REFUNDED => 'danger',
            ]);
        yield TextareaField::new('notes')->hideOnIndex();
        yield DateTimeField::new('paidAt', 'Payée le')->hideOnIndex();
        yield DateTimeField::new('shippedAt', 'Expédiée le')->hideOnIndex()->setFormTypeOption('input', 'datetime_immutable');
        yield DateTimeField::new('createdAt', 'Reçue le')->hideOnForm();
    }
}
