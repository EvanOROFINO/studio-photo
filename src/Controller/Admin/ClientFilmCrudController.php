<?php

namespace App\Controller\Admin;

use App\Entity\ClientFilm;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class ClientFilmCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ClientFilm::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Film livré')
            ->setEntityLabelInPlural('Films livrés (galeries clients)')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setHelp('index', 'Livrez le film fini à un client : collez un lien Vimeo/YouTube privé (non répertorié). Il n\'est visible que via le lien privé de la galerie du client.');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield AssociationField::new('gallery', 'Galerie client');
        yield TextField::new('title', 'Titre du film');
        yield UrlField::new('url', 'Lien Vimeo / YouTube (privé)')
            ->setHelp('Utilisez un lien "non répertorié" (unlisted) pour que seul votre client y accède.');
        yield TextField::new('duration', 'Durée')->hideOnIndex()->setRequired(false);
        yield UrlField::new('downloadUrl', 'Lien de téléchargement HD (optionnel)')
            ->setHelp('WeTransfer, Google Drive, lien S3… pour que le client garde le fichier source.')
            ->hideOnIndex()
            ->setRequired(false);
        yield TextField::new('source', 'Plateforme')->onlyOnIndex();
        yield TextareaField::new('description')->hideOnIndex()->setRequired(false);
        yield IntegerField::new('position', 'Ordre');
    }
}
