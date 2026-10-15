<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SeoController extends AbstractController
{
    /**
     * robots.txt généré : l'adresse du sitemap doit être absolue et suivre le domaine
     * du site courant (Photo ou Vidéo), ce qu'un fichier statique ne sait pas faire.
     */
    #[Route('/robots.txt', name: 'app_robots', methods: ['GET'])]
    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /login',
            'Disallow: /panier',
            'Disallow: /commande',
            'Disallow: /galerie-client',
            'Disallow: /_profiler',
            'Disallow: /_wdt',
            '',
            'Sitemap: '.$this->generateUrl('app_sitemap', [], UrlGeneratorInterface::ABSOLUTE_URL),
            '',
        ];

        return new Response(implode("\n", $lines), Response::HTTP_OK, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    /** Les navigateurs et robots réclament /favicon.ico même quand la page déclare une icône SVG. */
    #[Route('/favicon.ico', name: 'app_favicon', methods: ['GET'])]
    public function favicon(): RedirectResponse
    {
        return new RedirectResponse('/favicon.svg', Response::HTTP_MOVED_PERMANENTLY);
    }
}
