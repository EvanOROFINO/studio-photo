<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class LocaleController extends AbstractController
{
    private const ALLOWED = ['fr', 'en'];

    #[Route('/locale/{locale}', name: 'app_locale_switch', requirements: ['locale' => 'fr|en'])]
    public function switch(string $locale, Request $request): RedirectResponse
    {
        if (in_array($locale, self::ALLOWED, true)) {
            $request->getSession()->set('_locale', $locale);
        }

        $referer = $request->headers->get('referer');
        return new RedirectResponse($referer ?: $this->generateUrl('app_home'));
    }
}
