<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Tirage / Produit')
            ->setEntityLabelInPlural('Boutique — Produits')
            ->setDefaultSort(['position' => 'ASC', 'createdAt' => 'DESC'])
            ->setHelp('new', 'Stock = -1 → impression à la demande (illimité). 0 → rupture. Sinon, nombre d\'exemplaires disponibles.');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield ImageField::new('imageName', 'Aperçu')
            ->setBasePath('/uploads/products')
            ->onlyOnIndex();
        yield TextField::new('imageFile', 'Image')
            ->setFormType(VichImageType::class)
            ->setFormTypeOptions(['allow_delete' => false, 'download_uri' => false])
            ->onlyOnForms();
        yield TextField::new('title', 'Titre');
        yield TextField::new('format', 'Format (ex: 30×40 cm, papier mat)')->hideOnIndex();
        yield TextareaField::new('description')->hideOnIndex();
        yield MoneyField::new('price', 'Prix')->setCurrency('EUR')->setStoredAsCents(false);
        yield IntegerField::new('stock', 'Stock (-1 = illimité)');
        yield BooleanField::new('featured', 'À la une');
        yield BooleanField::new('published', 'Publié');
        yield IntegerField::new('position', 'Ordre');
        yield DateTimeField::new('createdAt', 'Créé le')->hideOnForm()->hideOnIndex();
    }
}
