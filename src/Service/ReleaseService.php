<?php

namespace App\Service;

use App\Repository\ReleaseRepository;
use App\Util\GraphUtil;

class ReleaseService
{

    private array $releases;

    public function __construct(
        private ReleaseRepository $releaseRepository,
    )
    {
        $this->releases = $this->releaseRepository->findAll();
    }

    public function getReleaseCount() : int
    {
        return count($this->releases);
    }

    public function graphData() : array
    {
        $result = [];

        $now = new \DateTime();
        $min = new \DateTime();
        $min->modify('-' . GraphUtil::DEFAULT_DAYS . ' day');

        foreach ($this->releases as $release) {
            $date = $release->getCreatedAt()->format('Y-m-d');
            $releaseDate = \DateTime::createFromFormat('Y-m-d', $date);
            if ($releaseDate >= $min && $releaseDate <= $now) {
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