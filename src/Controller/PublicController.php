<?php

namespace App\Controller;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PublicController extends AbstractController
{
    /**
     * @throws \DateMalformedStringException
     */
    #[Route('/', name: 'app_index')]
    public function index(Request $request, EventRepository $eventRepository): Response
    {
        $startDate = $request->query->get('start_date');
        $endDate = $request->query->get('end_date');

        $start = $startDate ? new \DateTime($startDate) : null;
        $end = $endDate ? new \DateTime($endDate) : null;

        $events = $eventRepository->findByDateRange($start, $end);

        return $this->render('public/index.html.twig', [
            'events' => $events,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }
}
