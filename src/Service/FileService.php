<?php

namespace App\Service;

use App\Entity\Release;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;

class FileService
{

    public function __construct(
        private ParameterBagInterface $parameterBag,
        private LoggerInterface $logger
    )
    {}

    public function createFolder(string $directory): bool
    {
        $path = $this->parameterBag->get('kernel.project_dir') . '/plugins';
        if (!file_exists($path)) {
            mkdir($path);
        }

        $path .= '/' . $directory;
        if (!file_exists($path)) {
            mkdir($path);
            return true;
        }
        return false;
    }

    public function getAllFiles() : array
    {
        try {
            $finder = new Finder();
            $plugins = $finder->directories()->in($this->parameterBag->get('kernel.project_dir'). '/plugins/');

            $result = [];

            foreach ($plugins as $plugin) {
                $finder = new Finder();
                $releases = $finder->files()->in($plugin->getRealPath());

                $filenames = [];

                foreach ($releases as $release) {
                    $filenames[$release->getFilename()] = $plugin->getRelativePathname() . '/' . $release->getFilename();
                }

                $result[$plugin->getFilename()] = $filenames;
            }

            return $result;
        }
        catch (DirectoryNotFoundException $exception){
            return [''];
        }
    }

    public function getRelease(?Release $release) : ?string
    {
        if (!$release || !$release->getApplication()) {
            return null;
        }

        return $this->parameterBag->get('kernel.project_dir') . '/plugins/' . $release->getFileName();
    }

    public static function downloadFile($file, $name): void
    {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.$name.'"');
        header('Content-Length: ' . filesize($file));

        readfile($file);
    }

}
