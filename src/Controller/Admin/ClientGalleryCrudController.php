<?php

namespace App\Controller\Admin;

use App\Entity\ClientGallery;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class ClientGalleryCrudController extends AbstractCrudController
{
    public function __construct(private readonly PasswordHasherFactoryInterface $hasherFactory)
    {
    }

    public static function getEntityFqcn(): string
    {
        return ClientGallery::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Galerie client')
            ->setEntityLabelInPlural('Galeries client')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setHelp('index', 'Espaces privés où vos clients téléchargent leurs photos après séance. Le lien et le mot de passe sont à envoyer au client.');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();

        yield TextField::new('title', 'Titre de la galerie')
            ->setHelp('Ex: "Mariage Sophie & Julien — 14/09/2024"');

        yield TextField::new('clientName', 'Nom du client');
        yield TextField::new('clientEmail', 'Email du client')->hideOnIndex();

        yield TextField::new('token', 'URL d\'accès')
            ->onlyOnIndex()
            ->formatValue(function ($value) {
                return $value ? '/galerie-client/'.$value : '';
            });

        yield TextField::new('plainPassword', 'Mot de passe')
            ->setFormType(PasswordType::class)
            ->setFormTypeOption('mapped', false)
            ->setFormTypeOption('required', $pageName === Crud::PAGE_NEW)
            ->setHelp($pageName === Crud::PAGE_EDIT ? 'Laisser vide pour ne pas changer.' : 'Choisissez un mot de passe à envoyer au client.')
            ->onlyOnForms();

        yield DateTimeField::new('shootDate', 'Date de la séance')
            ->setFormTypeOption('input', 'datetime_immutable')
            ->setRequired(false)
            ->hideOnIndex();

        yield DateTimeField::new('expiresAt', 'Expire le')
            ->setFormTypeOption('input', 'datetime_immutable')
            ->setHelp('La galerie devient inaccessible après cette date. Par défaut +6 mois.');

        yield TextareaField::new('welcomeMessage', 'Message d\'accueil')
            ->hideOnIndex()
            ->setHelp('Quelques mots affichés en haut de la galerie. Optionnel.');

        yield BooleanField::new('allowDownload', 'Téléchargement autorisé');
        yield BooleanField::new('active', 'Active');

        yield IntegerField::new('viewCount', 'Vues')->onlyOnIndex();
        yield DateTimeField::new('lastViewedAt', 'Dernière vue')->onlyOnIndex();
        yield DateTimeField::new('createdAt', 'Créée le')->hideOnForm();

        yield AssociationField::new('photos', 'Photos')
            ->onlyOnIndex()
            ->setTemplatePath('admin/fields/photos_count.html.twig');
    }

    public function createEntity(string $entityFqcn): ClientGallery
    {
        return new ClientGallery();
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->hashPlainPassword($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->hashPlainPassword($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    private function hashPlainPassword(ClientGallery $gallery): void
    {
        $form = $this->getContext()?->getRequest()->request->all('ClientGallery');
        $plain = $form['plainPassword'] ?? '';
        if ($plain !== '') {
            $hasher = $this->hasherFactory->getPasswordHasher('common');
            $gallery->setPasswordHash($hasher->hash($plain));
        }
    }
}
