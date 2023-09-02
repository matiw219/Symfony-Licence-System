<?php

namespace App\Controller\Hub;

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

class UserReleaseCrudController extends AbstractCrudController
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
            TextField::new('application'),
            TextField::new('version')
        ];

        match ($pageName === Crud::PAGE_DETAIL) {
            true => $res[] = TextareaField::new('description')->renderAsHtml(),
            false => $res[] = TextEditorField::new('description')
        };


        if ($pageName === Crud::PAGE_INDEX || $pageName === Crud::PAGE_DETAIL) {
            $res[] = TextField::new('admin');
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
                }))
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)

            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)

            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_RETURN)
            ->setPermissions([
                Crud::PAGE_NEW => 'ROLE_ADMIN',
                Crud::PAGE_EDIT => 'ROLE_ADMIN',
                Action::DELETE => 'ROLE_ADMIN',
                Action::BATCH_DELETE => 'ROLE_ADMIN'
            ]);
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $qb->addOrderBy('entity.application', 'DESC');
        $qb->addOrderBy('entity.createdAt', 'DESC');

        return $qb;
    }
}
