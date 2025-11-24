<?php

namespace App\Controller;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PublicController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(EventRepository $eventRepository): Response
    {
        return $this->render('public/index.html.twig', [
            'events' => $eventRepository->findAllFutureEvents(),
        ]);
    }
}
