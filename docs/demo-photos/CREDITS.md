# Photos et vidéos de démonstration

Ces images et vidéos servent **uniquement à présenter le site** avec un contenu cohérent par thème
(mariage, portrait, événement, grossesse & bébé, et les 4 catégories vidéo).
Ce ne sont **pas des réalisations du photographe** : elles seront remplacées par ses propres photos
et films (depuis l'admin, sans toucher au code).

Elles sont chargées par les fixtures (`src/DataFixtures/AppFixtures.php`) en développement uniquement ;
jamais en production, jamais pendant les tests.

## Photos

Source : [Pexels](https://www.pexels.com/), sous [licence Pexels](https://www.pexels.com/license/)
(usage commercial autorisé, attribution non requise). Redimensionnées à 1600 px maximum.

| Fichier | Thème | Photo d'origine |
|---|---|---|
| `mariage-ceremonie.jpg` | Mariages | https://www.pexels.com/photo/28322753/ |
| `mariage-emotion.jpg` | Mariages | https://www.pexels.com/photo/31254023/ |
| `mariage-voile.jpg` | Mariages | https://www.pexels.com/photo/7000008/ |
| `mariage-alliances.jpg` | Mariages | https://www.pexels.com/photo/34742353/ |
| `mariage-bouquet.jpg` | Mariages | https://www.pexels.com/photo/26811050/ |
| `mariage-danse.jpg` | Mariages | https://www.pexels.com/photo/2463421/ |
| `portrait-lumiere-naturelle.jpg` | Portraits | https://www.pexels.com/photo/31987756/ |
| `portrait-sourire.jpg` | Portraits | https://www.pexels.com/photo/3791554/ |
| `portrait-artistique.jpg` | Portraits | https://www.pexels.com/photo/144433/ |
| `evenement-conference.jpg` | Événements | https://www.pexels.com/photo/29708266/ |
| `evenement-soiree.jpg` | Événements | https://www.pexels.com/photo/31129059/ |
| `evenement-prise-de-parole.jpg` | Événements | https://www.pexels.com/photo/207716/ |
| `bebe-attente.jpg` | Grossesse & Bébé | https://www.pexels.com/photo/1556669/ |
| `bebe-maternite.jpg` | Grossesse & Bébé | https://www.pexels.com/photo/618923/ |
| `bebe-premiers-jours.jpg` | Grossesse & Bébé | https://www.pexels.com/photo/19760290/ |
| `bebe-petons.jpg` | Grossesse & Bébé | https://www.pexels.com/photo/325690/ |

## Vidéos

Vidéos publiques de tiers, **intégrées via le lecteur officiel YouTube** (rien n'est copié ni hébergé ici).
Elles restent la propriété de leurs auteurs. Vérifiées intégrables le 2026-10-14.

| Catégorie | Vidéo | Chaîne |
|---|---|---|
| Mariage | https://www.youtube.com/watch?v=KZdMSjAkiSk | Knoxland Films |
| Mariage | https://www.youtube.com/watch?v=sJTeEmSs2qo | Rowe Films |
| Mariage (site Photo) | https://www.youtube.com/watch?v=O7iWM-JCKyc | How To Film Weddings |
| Corporate | https://www.youtube.com/watch?v=YTOEgCxtMEY | Two Story Video Production |
| Clip & Brand | https://www.youtube.com/watch?v=9puSmd0Yr1k | TRIO Stories |
| Événement | https://www.youtube.com/watch?v=PhIBLF3zGJg | Velvet Innovation |
| Grossesse (site Photo) | https://www.youtube.com/watch?v=6bV5vvzAE10 | Stanley Parrish Jr. |
| Coulisses corporate (site Photo) | https://www.youtube.com/watch?v=MgMsppzfJ0g | Scott McKenna |

Si une vidéo est retirée par son auteur, l'emplacement s'affiche vide : remplacer l'URL depuis l'admin
(Showreel / Vidéos) ou dans les fixtures.

## Images du site (hors fixtures)

| Fichier | Source |
|---|---|
| `public/images/hero-evenement.jpg` | Fond de l'en-tête du Studio Vidéo : recadrage 16/9 de `evenement-soiree.jpg` (https://www.pexels.com/photo/31129059/) |
| `public/images/og-photo.jpg`, `og-video.jpg` | Aperçus de partage : recadrages de `mariage-ceremonie.jpg` et `mariage-emotion.jpg` |
