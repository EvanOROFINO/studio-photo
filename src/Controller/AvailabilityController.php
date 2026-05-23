<?php

namespace App\Controller;

use App\Service\AvailabilityService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AvailabilityController extends AbstractController
{
    public function __construct(private readonly AvailabilityService $availability)
    {
    }

    #[Route('/disponibilites', name: 'app_availability')]
    public function index(): Response
    {
        return $this->render('availability/index.html.twig');
    }

    #[Route('/api/disponibilites', name: 'app_availability_api', methods: ['GET'])]
    public function api(Request $request): JsonResponse
    {
        $fromInput = (string) $request->query->get('start', '');
        $toInput = (string) $request->query->get('end', '');

        try {
            $from = $fromInput !== '' ? new \DateTimeImmutable($fromInput) : new \DateTimeImmutable('first day of this month');
            $to = $toInput !== '' ? new \DateTimeImmutable($toInput) : new \DateTimeImmutable('+6 months');
        } catch (\Exception) {
            return new JsonResponse(['error' => 'Invalid date range'], 400);
        }

        return new JsonResponse($this->availability->getUnavailableEvents($from, $to));
    }
}
