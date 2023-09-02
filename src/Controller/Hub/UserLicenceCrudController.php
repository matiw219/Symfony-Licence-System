<?php

namespace App\Controller\Hub;

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

class UserLicenceCrudController extends AbstractCrudController
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
        if ($pageName === Crud::PAGE_EDIT) {
            $res = [TextField::new('licenceKey')];
            /** @var Licence $entity */
            $entity = $this->getContext()->getEntity()->getInstance();
            if ($entity->isRequireHost()) {
                $res[] = TextField::new('host');
            }
            if ($entity->isRequirePort()) {
                $res[] = IntegerField::new('port');
            }
            return $res;
        }
        $res = [
            TextField::new('licenceKey'),
            BooleanField::new('requireHost')->renderAsSwitch(false),
            TextField::new('host'),
            BooleanField::new('requirePort')->renderAsSwitch(false),
            IntegerField::new('port'),
            TextField::new('application'),
        ];
        if ($pageName === Crud::PAGE_INDEX || $pageName === Crud::PAGE_DETAIL) {
            $res[] = TextField::new('admin');
            $res[] = DateTimeField::new('createdAt');
        }
        return $res;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::new('detail', 'Detail')
                ->linkToCrudAction(Crud::PAGE_DETAIL))
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_INDEX, Action::NEW)

            ->remove(Crud::PAGE_DETAIL, Action::DELETE)

            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_RETURN)
            ->setPermissions([
                Crud::PAGE_NEW => 'ROLE_ADMIN',
                Action::DELETE => 'ROLE_ADMIN',
                Action::BATCH_DELETE => 'ROLE_ADMIN'
            ]);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('application')
            ->add('user')
            ->add('admin');
    }
}
