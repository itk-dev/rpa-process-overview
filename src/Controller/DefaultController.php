<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_default')]
    public function index(Request $request): JsonResponse
    {
        return $this->json([
            $this->generateUrl('process_overview_admin', referenceType: UrlGeneratorInterface::ABSOLUTE_URL),
            $this->generateUrl('process_overview_group_index', referenceType: UrlGeneratorInterface::ABSOLUTE_URL),
            $this->generateUrl('process_overview_index', referenceType: UrlGeneratorInterface::ABSOLUTE_URL),
            $request->query->all(),
        ]);
    }
}
