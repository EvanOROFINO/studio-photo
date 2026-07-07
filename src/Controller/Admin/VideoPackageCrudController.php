<?php

namespace App\Controller\Admin;

use App\Entity\VideoPackage;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class VideoPackageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return VideoPackage::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Forfait vidéo')
            ->setEntityLabelInPlural('Forfaits vidéo')
            ->setDefaultSort(['position' => 'ASC'])
            ->setSearchFields(['name', 'tagline']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield AssociationField::new('site', 'Site')
            ->setHelp('Sur quel site ce forfait apparaît (généralement Studio Vidéo).');
        yield TextField::new('name', 'Nom du forfait');
        yield TextField::new('tagline', 'Accroche')->hideOnIndex();
        yield IntegerField::new('price', 'Prix (€)');
        yield TextField::new('priceSuffix', 'Préfixe prix')
            ->setHelp('Ex: "à partir de"')->hideOnIndex();
        yield TextareaField::new('features', 'Fonctionnalités')
            ->setHelp('Une fonctionnalité par ligne. Chaque ligne devient une puce ✓.')
            ->hideOnIndex();
        yield TextField::new('deliveryTime', 'Délai de livraison')->hideOnIndex();
        yield BooleanField::new('featured', 'Mis en avant ⭐');
        yield BooleanField::new('isActive', 'Actif');
        yield IntegerField::new('position', 'Ordre');
    }
}
