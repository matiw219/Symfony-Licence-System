<?php

namespace App\Controller\Admin;

use App\Entity\Licence;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class LicenceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Licence::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
