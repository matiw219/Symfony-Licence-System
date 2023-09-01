<?php

namespace App\Controller\Admin;

use App\Entity\Licence;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class LicenceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Licence::class;
    }

    public function createEntity(string $entityFqcn)
    {
        $licence = new Licence();
        $licence->setAdmin($this->getUser());
        $licence->setCreatedAt(new \DateTimeImmutable());
        $licence->setLicenceKey(bin2hex(random_bytes(32)));
        return$licence;
    }

    public function configureFields(string $pageName): iterable
    {
        $res = [
            TextField::new('licenceKey'),
            TextField::new('note'),
            BooleanField::new('requireHost'),
            TextField::new('host'),
            BooleanField::new('requirePort'),
            IntegerField::new('port'),
            AssociationField::new('application'),
            AssociationField::new('user'),
            DateTimeField::new('createdAt')
        ];
        return $res;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::new('detail', 'Detail')
                ->linkToCrudAction(Crud::PAGE_DETAIL));
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('application')
            ->add('user')
            ->add('admin');
    }
}
