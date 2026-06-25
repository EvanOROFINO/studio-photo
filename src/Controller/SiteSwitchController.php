<?php

namespace App\Controller;

use App\Repository\SiteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SiteSwitchController extends AbstractController
{
    #[Route('/changer-de-site/{slug}', name: 'app_site_switch')]
    public function switch(string $slug, Request $request, SiteRepository $siteRepository): Response
    {
        $target = $siteRepository->findBySlug($slug);

        if ($target === null) {
            return $this->redirectToRoute('app_home');
        }

        $currentHost = $request->getHttpHost();
        $domain = $target->getDomain();

        // En production : si le site cible a un vrai domaine distinct, on y redirige
        $looksLikeRealDomain = $domain
            && str_contains($domain, '.')
            && !str_starts_with($domain, '127.')
            && !str_starts_with($domain, 'localhost')
            && $domain !== $currentHost;

        if ($looksLikeRealDomain) {
            $scheme = $request->isSecure() ? 'https' : 'http';
            return $this->redirect($scheme . '://' . $domain);
        }

        // En dev / staging (même host) : on bascule via la session
        $request->getSession()->set('_site_override', $slug);

        return $this->redirectToRoute('app_home');
    }
}
