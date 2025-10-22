<?php

namespace App\Twig;

use App\Service\FileManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    private ParameterBagInterface $params;
    private FileManager $fileManager;

    public function __construct(ParameterBagInterface $params, FileManager $fileManager)
    {
        $this->params = $params;
        $this->fileManager = $fileManager;
    }

    public function getGlobals(): array
    {
        return [
            'file_manager_shortcuts' => $this->fileManager->getRootDirectories(),
            'file_manager_base_path' => $this->params->get('file_manager.base_path'),
        ];
    }
}
