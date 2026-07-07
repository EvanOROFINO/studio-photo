<?php

namespace App\Controller\Admin;

use App\Entity\VideoCategory;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class VideoCategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return VideoCategory::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Catégorie vidéo')
            ->setEntityLabelInPlural('Catégories vidéo')
            ->setDefaultSort(['position' => 'ASC', 'name' => 'ASC'])
            ->setSearchFields(['name', 'description']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name', 'Nom');
        yield TextField::new('iconEmoji', 'Icône')
            ->setHelp('Un emoji (ex: 💍 pour mariage, 🏢 pour corporate, 🎵 pour clip)');
        yield TextField::new('slug')->hideOnForm();
        yield TextareaField::new('description')->hideOnIndex();
        yield BooleanField::new('isActive', 'Actif');
        yield IntegerField::new('position', 'Ordre');
    }
}
