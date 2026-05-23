<?php

namespace App\Controller\Admin;

use App\Entity\ClientPhoto;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ClientPhotoCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ClientPhoto::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Photo client')
            ->setEntityLabelInPlural('Photos clients')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setHelp('index', 'Ajoutez ici les photos à mettre dans une galerie client. Elles ne seront visibles que par le client via son lien privé.');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield AssociationField::new('gallery', 'Galerie');
        yield TextField::new('title', 'Titre (optionnel)')->hideOnIndex();
        yield TextField::new('imageFile', 'Fichier')
            ->setFormType(VichImageType::class)
            ->setFormTypeOptions([
                'allow_delete' => false,
                'download_uri' => false,
            ])
            ->onlyOnForms();
        yield TextField::new('originalName', 'Nom original')->onlyOnIndex();
        yield IntegerField::new('position', 'Ordre');
        yield DateTimeField::new('createdAt', 'Ajoutée le')->hideOnForm();
    }
}
