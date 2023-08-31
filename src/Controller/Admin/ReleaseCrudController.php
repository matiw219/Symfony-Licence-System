<?php

namespace App\Controller\Admin;

use App\Entity\Release;
use App\Service\FileService;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ReleaseCrudController extends AbstractCrudController
{

    public function __construct(
        private FileService $fileService
    )
    {}

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

        match ($pageName === Crud::PAGE_NEW || $pageName === Crud::PAGE_EDIT) {
            true => $res[] = ChoiceField::new('fileName')->setChoices([$this->fileService->getAllFiles()]),
            false => $res[] = TextField::new('fileName')
        };

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
                ->linkToCrudAction(Crud::PAGE_DETAIL))
            ->add(Crud::PAGE_INDEX, Action::new('download', 'Download')
                ->linkToRoute('app_release_download', function (Release $release) {
                    return ['id' => $release->getId()];
                }));
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $qb->addOrderBy('entity.application', 'DESC');
        $qb->addOrderBy('entity.createdAt', 'DESC');

        return $qb;
    }
}
