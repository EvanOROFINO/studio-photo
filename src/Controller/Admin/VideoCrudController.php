<?php

namespace App\Controller\Admin;

use App\Entity\Video;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class VideoCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Video::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Vidéo')
            ->setEntityLabelInPlural('Vidéos / Showreel')
            ->setDefaultSort(['position' => 'ASC', 'createdAt' => 'DESC'])
            ->setHelp('new', 'Collez simplement l\'URL YouTube ou Vimeo. La source et l\'ID sont détectés automatiquement.');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('title', 'Titre');
        yield UrlField::new('url', 'URL YouTube / Vimeo')
            ->setHelp('Ex : https://www.youtube.com/watch?v=… ou https://vimeo.com/123456');
        yield TextField::new('source', 'Plateforme')->onlyOnIndex();
        yield TextField::new('externalId', 'ID vidéo')->onlyOnIndex();
        yield TextareaField::new('description')->hideOnIndex()->setRequired(false);
        yield BooleanField::new('featured', 'À la une');
        yield BooleanField::new('published', 'Publié');
        yield IntegerField::new('position', 'Ordre');
        yield DateTimeField::new('createdAt', 'Ajoutée le')->hideOnForm()->hideOnIndex();
    }
}
