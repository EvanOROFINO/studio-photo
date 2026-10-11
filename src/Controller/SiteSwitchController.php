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

        // En production : on n'est redirigé vers le vrai domaine du site cible que si
        // l'on est déjà servi depuis l'un des vrais domaines (et non un domaine de staging).
        $current = $siteRepository->findByDomain($currentHost);
        $onProductionDomain = $current !== null && strcasecmp((string) $current->getDomain(), $currentHost) === 0;

        if ($onProductionDomain && $domain && strcasecmp($domain, $currentHost) !== 0) {
            $scheme = $request->isSecure() ? 'https' : 'http';
            return $this->redirect($scheme . '://' . $domain);
        }

        // Ailleurs (localhost, staging, lien de démo via tunnel…) : on bascule via la session
        $request->getSession()->set('_site_override', $slug);

        return $this->redirectToRoute('app_home');
    }
}
