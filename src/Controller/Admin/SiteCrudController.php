<?php

namespace App\Controller\Admin;

use App\Entity\Site;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SiteCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Site::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Site')
            ->setEntityLabelInPlural('Sites')
            ->setDefaultSort(['position' => 'ASC'])
            ->setSearchFields(['name', 'domain']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield ChoiceField::new('slug')
            ->setChoices(Site::SLUGS)
            ->setHelp('Identifiant interne du site (non modifiable après création).');
        yield TextField::new('name', 'Nom du site');
        yield TextField::new('iconEmoji', 'Icône emoji');
        yield TextField::new('domain', 'Domaine production');
        yield TextField::new('domainStaging', 'Domaine staging / dev')
            ->setHelp('Ex: 127.0.0.1:8000 ou video.localhost:8000')
            ->hideOnIndex();
        yield TextareaField::new('tagline', 'Slogan')->hideOnIndex();
        yield TextField::new('primaryColor', 'Couleur principale')
            ->setHelp('Format hexa #RRGGBB');
        yield TextField::new('accentColor', 'Couleur accent')
            ->setHelp('Format hexa #RRGGBB');
        yield BooleanField::new('isActive', 'Actif');
        yield BooleanField::new('isDefault', 'Site par défaut')
            ->setHelp('Site utilisé en fallback si le host ne correspond à aucun.');
        yield IntegerField::new('position', 'Ordre');
    }
}
