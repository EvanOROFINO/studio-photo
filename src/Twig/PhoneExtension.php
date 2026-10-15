<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class PhoneExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('whatsapp_number', [$this, 'whatsappNumber']),
        ];
    }

    /**
     * Numéro au format attendu par wa.me : international, chiffres uniquement.
     * « 06 12 34 56 78 » devient « 33612345678 » (le 0 initial est remplacé par l'indicatif français).
     */
    public function whatsappNumber(?string $phone): string
    {
        $digits = (string) preg_replace('/\D+/', '', (string) $phone);

        if (str_starts_with($digits, '00')) {
            return substr($digits, 2);
        }
        if (str_starts_with($digits, '0')) {
            return '33'.substr($digits, 1);
        }

        return $digits;
    }
}
