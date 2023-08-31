<?php

namespace App\Controller\Admin;

use App\Entity\Release;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use phpDocumentor\Reflection\Types\Collection;
use function Sodium\add;

class ReleaseCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Release::class;
    }

    public function createEntity(string $entityFqcn)
    {
        $release = new Release();
        $release->setAdmin($this->getUser());
        $release->setCreatedAt(new \DateTimeImmutable());
        return $release;
    }

    public function configureFields(string $pageName): iterable
    {
        $res = [
            AssociationField::new('application'),
            TextField::new('version')
        ];

        match ($pageName === Crud::PAGE_DETAIL) {
            true => $res[] = TextareaField::new('description')->renderAsHtml(),
            false => $res[] = TextEditorField::new('description')
        };

        $res[] = TextField::new('fileName');
        if ($pageName === Crud::PAGE_INDEX || $pageName === Crud::PAGE_DETAIL) {
            $res[] = AssociationField::new('admin');
            $res[] = DateTimeField::new('createdAt');
        }

        return $res;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add('application')->add('admin');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::new('detail', 'Detail')
                ->linkToCrudAction(Crud::PAGE_DETAIL));
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $qb->addOrderBy('entity.application', 'DESC');
        $qb->addOrderBy('entity.createdAt', 'DESC');

        return $qb;
    }
}
