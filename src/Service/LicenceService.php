<?php

namespace App\Service;

use App\Repository\LicenceRepository;
use App\Util\GraphUtil;

class LicenceService
{

    private array $licences;

    public function __construct(
        private LicenceRepository $licenceRepository,
    )
    {
        $this->licences = $this->licenceRepository->findAll();
    }

    public function getLicenceCount() : int
    {
        return count($this->licences);
    }

    public function graphData() : array
    {
        $result = [];

        $now = new \DateTime();
        $min = new \DateTime();
        $min->modify('-' . GraphUtil::DEFAULT_DAYS . ' day');

        foreach ($this->licences as $licence) {
            $date = $licence->getCreatedAt()->format('Y-m-d');
            $licenceDate = \DateTime::createFromFormat('Y-m-d', $date);
            if ($licenceDate >= $min && $licenceDate <= $now) {
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