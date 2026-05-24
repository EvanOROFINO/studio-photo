<?php

namespace App\Controller\Admin;

use App\Entity\Coupon;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CouponCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Coupon::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Code promo')
            ->setEntityLabelInPlural('Codes promo')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setHelp('new', 'Le code sera automatiquement converti en MAJUSCULES. Exemple : BIENVENUE10, ETE20, NOEL2024.');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('code', 'Code')
            ->setHelp('Lettres majuscules, chiffres, _ et - uniquement.');
        yield ChoiceField::new('type', 'Type')
            ->setChoices(Coupon::TYPES);
        yield NumberField::new('value', 'Valeur')
            ->setHelp('% si type "Pourcentage", € si type "Montant fixe".');
        yield MoneyField::new('minAmount', 'Montant minimum (€)')
            ->setCurrency('EUR')->setStoredAsCents(false)->setRequired(false)
            ->setHelp('Laisser vide pour aucun minimum.');
        yield IntegerField::new('maxUses', 'Utilisations max')
            ->setRequired(false)
            ->setHelp('Laisser vide pour illimité.');
        yield IntegerField::new('usedCount', 'Déjà utilisé')->hideOnForm();
        yield DateTimeField::new('validFrom', 'Valide à partir du')
            ->setFormTypeOption('input', 'datetime_immutable')->setRequired(false);
        yield DateTimeField::new('validUntil', 'Valide jusqu\'au')
            ->setFormTypeOption('input', 'datetime_immutable')->setRequired(false);
        yield BooleanField::new('active', 'Actif');
        yield DateTimeField::new('createdAt', 'Créé le')->hideOnForm()->hideOnIndex();
    }
}
