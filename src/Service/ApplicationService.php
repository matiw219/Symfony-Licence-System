<?php

namespace App\Service;

use App\Repository\ApplicationRepository;
use App\Util\GraphUtil;

class ApplicationService
{

    private array $apps;

    public function __construct(
        private ApplicationRepository $applicationRepository,
    )
    {
        $this->apps = $this->applicationRepository->findAll();
    }

    public function getApplicationCount() : int
    {
        return count($this->apps);
    }

    public function graphData() : array
    {
        $result = [];

        $now = new \DateTime();
        $min = new \DateTime();
        $min->modify('-' . GraphUtil::DEFAULT_DAYS . ' day');

        foreach ($this->apps as $app) {
            $date = $app->getCreatedAt()->format('Y-m-d');
            $appDate = \DateTime::createFromFormat('Y-m-d', $date);
            if ($appDate >= $min && $appDate <= $now) {
                if (!array_key_exists($date, $result)) {
                    $result[$date] = 1;
                }
                else {
                    $result[$date]++;
                }
            }
        }

        return $result;
    }

}