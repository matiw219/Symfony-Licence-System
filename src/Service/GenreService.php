<?php

namespace App\Service;

use App\Repository\GenreRepository;

class GenreService
{

    private array $genres;

    public function __construct(
        private GenreRepository $genreRepository,
    )
    {
        $this->genres = $this->genreRepository->findAll();
    }

    public function getGenreCount() : int
    {
        return count($this->genres);
    }

}