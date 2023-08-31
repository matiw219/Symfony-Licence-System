<?php

namespace App\Controller\Admin;

use App\Entity\Genre;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class GenreCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Genre::class;
    }

    public function createEntity(string $entityFqcn)
    {
        $genre = new Genre();
        $genre->setAdmin($this->getUser());
        $genre->setCreatedAt(new \DateTimeImmutable());
        return $genre;
    }

    public function configureFields(string $pageName): iterable
    {
        $res = [
            TextField::new('name')
        ];
        if ($pageName === Crud::PAGE_EDIT || $pageName === Crud::PAGE_NEW) {
            return $res;
        }
        $res[] = AssociationField::new('admin');
        $res[] = DateTimeField::new('createdAt');
        return $res;
    }


}
