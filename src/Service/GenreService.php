<?php

namespace App\Service;

use App\Repository\GenreRepository;

class GenreService
{

    public function __construct(
        private GenreRepository $genreRepository
    )
    {}

    public function getGenreCount() : int
    {
        return $this->genreRepository->count([]);
    }

}