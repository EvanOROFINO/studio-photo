<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ArticleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Article::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Article')
            ->setEntityLabelInPlural('Articles')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setHelp('new', 'Le contenu accepte le Markdown : **gras**, *italique*, # Titres, [liens](url), ![images](url), listes…');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('title', 'Titre');
        yield TextField::new('slug')->hideOnForm();
        yield AssociationField::new('category', 'Catégorie');
        yield AssociationField::new('author', 'Auteur')->hideOnIndex();

        yield ImageField::new('coverImageName', 'Couverture')
            ->setBasePath('/uploads/articles')
            ->onlyOnIndex();
        yield TextField::new('coverImageFile', 'Image de couverture')
            ->setFormType(VichImageType::class)
            ->setFormTypeOptions(['allow_delete' => false, 'download_uri' => false])
            ->onlyOnForms();

        yield TextareaField::new('excerpt', 'Extrait (résumé court)')
            ->setHelp('Apparaît dans la liste des articles et les partages sociaux.')
            ->hideOnIndex();

        yield TextareaField::new('content', 'Contenu (Markdown)')
            ->setFormTypeOptions(['attr' => ['rows' => 18, 'style' => 'font-family: monospace;']])
            ->hideOnIndex();

        yield TextField::new('metaTitle', 'Meta title (SEO)')->hideOnIndex()->setRequired(false);
        yield TextField::new('metaDescription', 'Meta description (SEO)')->hideOnIndex()->setRequired(false);

        yield BooleanField::new('published', 'Publié');
        yield DateTimeField::new('publishedAt', 'Publié le')->hideOnForm();
        yield IntegerField::new('viewCount', 'Vues')->onlyOnIndex();
        yield DateTimeField::new('createdAt', 'Créé le')->hideOnForm()->hideOnIndex();
    }
}
