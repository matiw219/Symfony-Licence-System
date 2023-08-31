<?php

namespace App\Controller\Admin;

use App\Entity\Application;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ApplicationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Application::class;
    }

    public function createEntity(string $entityFqcn)
    {
        $app = new Application();
        $app->setCreatedAt(new \DateTimeImmutable());
        $app->setAdmin($this->getUser());
        return $app;
    }

    public function configureFields(string $pageName): iterable
    {
        $res = [
            TextField::new('name'),
            TextField::new('videoUrl'),
        ];

        if ($pageName === Crud::PAGE_DETAIL) {
            $res[] = TextareaField::new('description')->renderAsHtml();
        }
        else {
            $res[] = TextEditorField::new('description');
        }

        $res[] = AssociationField::new('genre');
        $res[] = NumberField::new('cost');

        if ($pageName !== Crud::PAGE_NEW && $pageName !== Crud::PAGE_EDIT) {
            $res[] = AssociationField::new('admin');
            $res[] = DateTimeField::new('createdAt');
        }
        return $res;
    }


    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::new('detail', 'Detail')
                ->linkToCrudAction(Crud::PAGE_DETAIL));
    }
}
