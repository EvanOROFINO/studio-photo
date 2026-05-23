<?php

namespace App\Controller\Admin;

use App\Entity\Booking;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class BookingCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Booking::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Réservation')
            ->setEntityLabelInPlural('Réservations')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setHelp('index', 'Réservations en ligne avec acompte payé via Stripe. Les statuts évoluent automatiquement après paiement.');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->disable(Action::NEW);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('reference', 'Réf.');
        yield AssociationField::new('service', 'Prestation');
        yield TextField::new('clientName', 'Client');
        yield TextField::new('clientEmail', 'Email')->hideOnIndex();
        yield TextField::new('clientPhone', 'Téléphone')->hideOnIndex();
        yield DateField::new('eventDate', 'Date séance');
        yield TextField::new('location', 'Lieu')->hideOnIndex();
        yield MoneyField::new('amountTotal', 'Total')->setCurrency('EUR')->setStoredAsCents(false);
        yield MoneyField::new('depositAmount', 'Acompte')->setCurrency('EUR')->setStoredAsCents(false);
        yield ChoiceField::new('status', 'Statut')
            ->setChoices(Booking::STATUSES)
            ->renderAsBadges([
                Booking::STATUS_PENDING => 'warning',
                Booking::STATUS_PAID => 'success',
                Booking::STATUS_CONFIRMED => 'primary',
                Booking::STATUS_CANCELLED => 'secondary',
                Booking::STATUS_REFUNDED => 'danger',
            ]);
        yield TextareaField::new('notes')->hideOnIndex();
        yield TextField::new('stripeSessionId', 'Stripe session')->hideOnIndex()->onlyOnDetail();
        yield TextField::new('stripePaymentIntentId', 'Stripe PI')->hideOnIndex()->onlyOnDetail();
        yield DateTimeField::new('paidAt', 'Payée le')->hideOnIndex();
        yield DateTimeField::new('createdAt', 'Reçue le')->hideOnForm();
    }
}
