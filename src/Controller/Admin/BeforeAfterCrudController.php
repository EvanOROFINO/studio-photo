<?php

namespace App\Controller\Admin;

use App\Entity\BeforeAfter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichImageType;

class BeforeAfterCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return BeforeAfter::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Avant / Après')
            ->setEntityLabelInPlural('Avant / Après')
            ->setDefaultSort(['position' => 'ASC'])
            ->setHelp('index', 'Comparaisons visuelles avant / après retouche, idéales pour démontrer votre travail.');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('title', 'Titre');
        yield TextareaField::new('description')->hideOnIndex();

        yield ImageField::new('beforeImageName', 'Avant')->setBasePath('/uploads/before-after')->onlyOnIndex();
        yield TextField::new('beforeImageFile', 'Photo "AVANT"')
            ->setFormType(VichImageType::class)
            ->setFormTypeOptions(['allow_delete' => false, 'download_uri' => false])
            ->onlyOnForms();

        yield ImageField::new('afterImageName', 'Après')->setBasePath('/uploads/before-after')->onlyOnIndex();
        yield TextField::new('afterImageFile', 'Photo "APRÈS"')
            ->setFormType(VichImageType::class)
            ->setFormTypeOptions(['allow_delete' => false, 'download_uri' => false])
            ->onlyOnForms();

        yield IntegerField::new('position', 'Ordre');
        yield BooleanField::new('published', 'Publié');
        yield DateTimeField::new('createdAt', 'Créé le')->hideOnForm()->hideOnIndex();
    }
}
