<?php

namespace App\Controller\Admin;

use App\Entity\Photo;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichImageType;

class PhotoCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Photo::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Photo')
            ->setEntityLabelInPlural('Photos')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPageTitle('index', 'Toutes les photos')
            ->setPageTitle('new', 'Ajouter une photo');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();

        yield TextField::new('title', 'Titre');

        yield AssociationField::new('category', 'Catégorie');

        yield ImageField::new('imageName', 'Aperçu')
            ->setBasePath('/uploads/photos')
            ->onlyOnIndex();

        yield TextField::new('imageFile', 'Fichier image')
            ->setFormType(VichImageType::class)
            ->setFormTypeOptions([
                'allow_delete' => false,
                'download_uri' => false,
            ])
            ->onlyOnForms();

        yield TextareaField::new('description', 'Description')
            ->hideOnIndex();

        yield BooleanField::new('featured', 'À la une');

        yield DateTimeField::new('takenAt', 'Prise de vue')
            ->hideOnIndex()
            ->setFormTypeOption('input', 'datetime_immutable');

        yield DateTimeField::new('createdAt', 'Ajoutée le')
            ->hideOnForm();
    }
}
