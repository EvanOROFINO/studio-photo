<?php

namespace App\Controller\Admin;

use App\Entity\Testimonial;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichImageType;

class TestimonialCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Testimonial::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Témoignage')
            ->setEntityLabelInPlural('Témoignages')
            ->setDefaultSort(['position' => 'ASC', 'createdAt' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield ImageField::new('avatarName', 'Avatar')
            ->setBasePath('/uploads/avatars')
            ->onlyOnIndex();
        yield TextField::new('avatarFile', 'Photo / Avatar (optionnel)')
            ->setFormType(VichImageType::class)
            ->setFormTypeOptions(['allow_delete' => true, 'download_uri' => false])
            ->onlyOnForms();
        yield TextField::new('authorName', 'Auteur');
        yield TextField::new('authorRole', 'Rôle / Type de projet')->hideOnIndex();
        yield TextareaField::new('content', 'Témoignage');
        yield ChoiceField::new('rating', 'Note')
            ->setChoices([
                '⭐⭐⭐⭐⭐ (5)' => 5,
                '⭐⭐⭐⭐ (4)' => 4,
                '⭐⭐⭐ (3)' => 3,
                '⭐⭐ (2)' => 2,
                '⭐ (1)' => 1,
            ]);
        yield IntegerField::new('position', 'Ordre');
        yield BooleanField::new('published', 'Publié');
        yield DateTimeField::new('createdAt', 'Reçu le')->hideOnForm();
    }
}
