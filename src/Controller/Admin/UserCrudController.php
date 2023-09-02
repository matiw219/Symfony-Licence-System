<?php

namespace App\Controller\Admin;

use App\Constraint\PasswordConstraint;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class UserCrudController extends AbstractCrudController
{

    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher,
    ){}

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $res = [
            EmailField::new('email')
                ->setFormType(EmailType::class),
            TextField::new('username')->setFormTypeOptions([
                    'constraints' => [
                        new Regex([
                            'pattern' => '/^[^0-9].*$/',
                            'message' => 'Username cannot start with a number'
                        ]),
                        new Regex([
                            'pattern' => '/^[a-zA-z0-9_]+$/',
                            'message' => 'Username can only consist of letters, numbers and the underscore'
                        ]),
                        new Length([
                            'min' => 6,
                            'max' => 32,
                            'minMessage' => 'Your username must be at least {{ limit }} characters long',
                            'maxMessage' => 'Your username is too long'
                        ]),
                    ]
                ]
            ),
        ];

        if ($pageName === Crud::PAGE_NEW || $pageName === Crud::PAGE_EDIT) {
            $res[] = TextField::new('password')
                ->setFormType(PasswordType::class)
                ->setFormTypeOptions([
                    'constraints' => [
                        new NotBlank([
                            'message' => 'This field cannot be empty'
                        ]),
                        new Length([
                            'min' => 12,
                            'max' => 40,
                            'minMessage' => 'Your password must be at least {{ limit }} characters long',
                            'maxMessage' => 'Your password is too long'
                        ]),
                        new PasswordConstraint()
                    ]
                ]);
        }

        $res[] = CollectionField::new('roles');

        return $res;
    }


    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add('roles');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::new('detail', 'Detail')
                ->linkToCrudAction(Crud::PAGE_DETAIL));
    }

    public function createEditFormBuilder( EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context ): FormBuilderInterface {
        $plainPassword = $entityDto->getInstance()?->getPassword();
        $formBuilder = parent::createEditFormBuilder($entityDto, $formOptions, $context);
        $this->addEncodePasswordEventListener($formBuilder, $plainPassword);
        return $formBuilder;
    }

    public function createNewFormBuilder( EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context ): FormBuilderInterface {
        $formBuilder = parent::createNewFormBuilder($entityDto, $formOptions, $context);
        $this->addEncodePasswordEventListener($formBuilder);
        return $formBuilder;
    }

    protected function addEncodePasswordEventListener( FormBuilderInterface $formBuilder, $plainPassword = null ): void {
        $formBuilder->addEventListener( FormEvents::SUBMIT, function (FormEvent $event) use ($plainPassword) {
            /** @var User $user */
            $user = $event->getData();
            if ($user->getPassword() !== $plainPassword) {
                $user->setPassword( $this->userPasswordHasher->hashPassword($user, $user->getPassword()));
            }
        } );
    }

}
