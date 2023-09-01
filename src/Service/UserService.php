<?php

namespace App\Service;

use App\Repository\UserRepository;
use Doctrine\ORM\NonUniqueResultException;

class UserService
{

    public function __construct(
        private UserRepository $userRepository
    )
    {}

    public function getUserCount() : int
    {
        return $this->userRepository->count([]);
    }

    public function getAdminCount() : int {
        $qb = $this->userRepository->createQueryBuilder('u');

        $qb->select('COUNT(u.id)')
            ->where($qb->expr()->like('u.roles', ':role'))
            ->setParameter('role', '%"ROLE_ADMIN"%');

        try {
            return $qb->getQuery()->getSingleScalarResult();
        } catch (NonUniqueResultException $e) {
            return 0;
        }
    }

}