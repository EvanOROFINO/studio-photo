<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\ArticleCategory;
use App\Entity\BlockedDate;
use App\Entity\Booking;
use App\Entity\Category;
use App\Entity\ClientGallery;
use App\Entity\ClientPhoto;
use App\Entity\ContactRequest;
use App\Entity\Coupon;
use App\Entity\NewsletterSubscriber;
use App\Entity\Photo;
use App\Entity\Product;
use App\Entity\Service;
use App\Entity\Testimonial;
use App\Entity\User;
use App\Entity\Video;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $userHasher,
        private readonly PasswordHasherFactoryInterface $hasherFactory,
        private readonly string $clientGalleriesDir,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // --- Admin user ---------------------------------------------------
        $admin = new User();
        $admin->setEmail('admin@studio-photo.local');
        $admin->setFullName('Admin Studio');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->userHasher->hashPassword($admin, 'admin'));
        $manager->persist($admin);

        // --- Categories ---------------------------------------------------
        $categoriesData = [
            ['Mariages', 'Des images sincères pour votre plus beau jour. Du préparatif à la dernière danse.', 1],
            ['Portraits', 'Séances individuelles, en duo ou en famille — en studio ou en extérieur.', 2],
            ['Événements', 'Soirées d\'entreprise, lancements, séminaires : capter l\'énergie du moment.', 3],
            ['Grossesse & Bébé', 'Des moments précieux à immortaliser avec douceur.', 4],
        ];

        $categories = [];
        foreach ($categoriesData as [$name, $description, $position]) {
            $category = new Category();
            $category->setName($name);
            $category->setDescription($description);
            $category->setPosition($position);
            $manager->persist($category);
            $categories[] = $category;
        }

        // --- Photos -------------------------------------------------------
        $photosByCategory = [
            'Mariages' => [
                ['Cérémonie au coucher du soleil', '2024-09-14'],
                ['Premier regard', '2024-08-22'],
                ['Lancer de bouquet', '2024-07-10'],
                ['Discours du témoin', '2024-06-29'],
                ['Danse d\'ouverture', '2024-05-18'],
                ['Détails de la robe', '2024-09-14'],
            ],
            'Portraits' => [
                ['Portrait corporate', '2025-01-12'],
                ['Maternité en lumière douce', '2024-11-30'],
                ['Couple en automne', '2024-10-15'],
                ['Portrait artistique', '2025-02-20'],
            ],
            'Événements' => [
                ['Conférence tech 2024', '2024-11-08'],
                ['Soirée de lancement', '2024-12-05'],
                ['Remise de prix', '2025-01-25'],
            ],
            'Grossesse & Bébé' => [
                ['Attendre en douceur', '2025-02-14'],
                ['Premiers jours', '2025-03-02'],
                ['Petites mains', '2025-03-10'],
            ],
        ];

        $featuredCount = 0;
        foreach ($categories as $category) {
            foreach ($photosByCategory[$category->getName()] ?? [] as $i => [$title, $date]) {
                $photo = new Photo();
                $photo->setTitle($title);
                $photo->setCategory($category);
                $photo->setTakenAt(new \DateTimeImmutable($date));
                $photo->setFeatured($featuredCount < 6 && $i === 0);
                $manager->persist($photo);
                if ($photo->isFeatured()) {
                    $featuredCount++;
                }
            }
        }

        // --- Services -----------------------------------------------------
        $services = [];
        $servicesData = [
            ['Reportage mariage', 'Couverture intégrale de votre journée, de la préparation à la fin de soirée. Plus de 500 photos retouchées livrées en galerie privée sous 6 semaines.', '1200', 'Journée complète (8h+)', 'bi-heart-fill', 1],
            ['Séance portrait', 'Séance individuelle ou en duo en studio ou en extérieur. 1h de shooting, 30 photos retouchées au choix.', '180', '1 heure', 'bi-person-fill', 2],
            ['Séance famille', 'Séance en extérieur d\'1h30, jusqu\'à 6 personnes. 40 photos retouchées livrées en galerie.', '290', '1h30', 'bi-people-fill', 3],
            ['Événement professionnel', 'Couverture photographique de votre événement d\'entreprise. Livraison express sous 48h pour la presse.', '450', 'Demi-journée', 'bi-building', 4],
            ['Grossesse & nouveau-né', 'Séance douce en studio ou à domicile. 2h pour capturer ces instants uniques. 50 photos retouchées.', '320', '2 heures', 'bi-flower2', 5],
            ['Shooting produit', 'Mise en valeur de vos produits sur fond neutre ou mise en scène. Idéal e-commerce.', '350', 'Demi-journée', 'bi-bag-fill', 6],
        ];

        foreach ($servicesData as [$title, $description, $price, $duration, $icon, $position]) {
            $service = new Service();
            $service->setTitle($title);
            $service->setDescription($description);
            $service->setPriceFrom($price);
            $service->setDuration($duration);
            $service->setIcon($icon);
            $service->setPosition($position);
            $service->setActive(true);
            $manager->persist($service);
            $services[] = $service;
        }

        // --- Sample bookings ---------------------------------------------
        $bookingsData = [
            ['Léa & Tom Dubois', 'lea.tom@example.fr', '0612345678', '+45 days', 'Domaine du Cèdre, Beaujolais', 'Mariage en plein air, 80 invités. Cérémonie laïque à 17h.', 0, Booking::STATUS_PAID],
            ['Camille Robert', 'camille.robert@example.fr', '0623456789', '+12 days', 'Lyon 6ème, studio Tête d\'Or', 'Séance corporate pour mon site et LinkedIn.', 1, Booking::STATUS_PAID],
            ['Famille Bertrand', 'bertrand.famille@example.fr', '0634567890', '+20 days', 'Parc de Gerland, Lyon', 'Séance famille avec 2 enfants (4 et 7 ans).', 2, Booking::STATUS_PENDING],
        ];
        foreach ($bookingsData as [$name, $email, $phone, $whenMod, $location, $notes, $serviceIdx, $status]) {
            $service = $services[$serviceIdx];
            $total = (float) $service->getPriceFrom();
            $deposit = round($total * 0.30, 2);

            $b = new Booking();
            $b->setService($service);
            $b->setClientName($name);
            $b->setClientEmail($email);
            $b->setClientPhone($phone);
            $b->setEventDate(new \DateTimeImmutable($whenMod));
            $b->setLocation($location);
            $b->setNotes($notes);
            $b->setAmountTotal((string) $total);
            $b->setDepositAmount((string) $deposit);
            $b->setStatus($status);
            if ($status === Booking::STATUS_PAID) {
                $b->setPaidAt(new \DateTimeImmutable('-2 days'));
                $b->setStripeSessionId('demo_'.bin2hex(random_bytes(8)));
            }
            $manager->persist($b);
        }

        // --- Blocked dates (vacations, training) -------------------------
        $blockedData = [
            ['+10 days', '+12 days', 'Congés annuels'],
            ['+25 days', null, 'Formation Lightroom avancé'],
            ['+50 days', '+52 days', 'Week-end off'],
            ['+90 days', '+97 days', 'Vacances été'],
        ];
        foreach ($blockedData as [$start, $end, $reason]) {
            $b = new BlockedDate();
            $b->setStartDate(new \DateTimeImmutable($start));
            if ($end !== null) {
                $b->setEndDate(new \DateTimeImmutable($end));
            }
            $b->setReason($reason);
            $manager->persist($b);
        }

        // --- Testimonials -------------------------------------------------
        $testimonialsData = [
            ['Sophie & Julien', 'Mariés en septembre 2024', 'Un photographe d\'une douceur incroyable. Il a su se faire oublier toute la journée, et le résultat est magique : des images sincères, pleines d\'émotion. Nos témoins et nos parents nous demandent encore à les revoir. Mille mercis !', 5, 1],
            ['Camille L.', 'Séance grossesse', 'Je redoutais cette séance, je me trouvais peu à l\'aise, mais j\'ai été mise en confiance immédiatement. Les photos sont sublimes, naturelles, et je m\'y retrouve totalement. Je recommande les yeux fermés.', 5, 2],
            ['Mathieu D.', 'Directeur — Agence Lyon', 'Très professionnel, ponctuel, à l\'écoute. Nos photos d\'équipe et de bureau ont été livrées en moins d\'une semaine. Qualité au rendez-vous, ça change vraiment du photographe d\'entreprise lambda.', 5, 3],
            ['Famille Bernard', 'Séance famille', 'Trois enfants en bas âge, et pourtant des images posées et naturelles. Le photographe a une vraie patience avec les enfants. Notre album est devenu notre cadeau préféré pour les grands-parents.', 5, 4],
            ['Lou M.', 'Portrait artistique', 'Une vraie démarche d\'artiste. On parle, on cherche ensemble, et le résultat est bien au-delà d\'une simple "photo de profil". Mes portraits ont totalement boosté mon image sur LinkedIn.', 5, 5],
            ['Anne & Pierre', 'Mariage juin 2024', 'On voulait du naturel, du vrai, on a eu mieux que ça. Six semaines après la cérémonie, on en parle encore. Les photos racontent vraiment notre journée, sans chichi.', 5, 6],
        ];

        foreach ($testimonialsData as [$author, $role, $content, $rating, $position]) {
            $testimonial = new Testimonial();
            $testimonial->setAuthorName($author);
            $testimonial->setAuthorRole($role);
            $testimonial->setContent($content);
            $testimonial->setRating($rating);
            $testimonial->setPosition($position);
            $testimonial->setPublished(true);
            $manager->persist($testimonial);
        }

        // --- Demo showreel videos ----------------------------------------
        $videosData = [
            ['Highlights — Mariage Sophie & Julien', 'Le résumé de leur journée, condensé en 3 minutes.', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', true, 1],
            ['Séance grossesse — Camille', 'Un moment d\'intimité capturé en lumière douce.', 'https://www.youtube.com/watch?v=jNQXAC9IVRw', false, 2],
            ['Behind the scenes — Reportage corporate', 'Coulisses d\'un shooting d\'entreprise à Lyon.', 'https://www.youtube.com/watch?v=9bZkp7q19f0', false, 3],
        ];
        foreach ($videosData as [$title, $description, $url, $featured, $position]) {
            $v = new Video();
            $v->setTitle($title);
            $v->setDescription($description);
            $v->setUrl($url);
            $v->setFeatured($featured);
            $v->setPosition($position);
            $v->setPublished(true);
            $manager->persist($v);
        }

        // --- Boutique : tirages d'art -----------------------------------
        $productsData = [
            ['Sérénité', 'Tirage fine art d\'un paysage de montagne au lever du soleil. Imprimé sur papier Hahnemühle Photo Rag 308 g, signé et numéroté.', '30×40 cm — papier mat', '89.00', 8, true, 1],
            ['Premier regard', 'Photographie de mariage capturée à l\'instant exact où les mariés se découvrent. Tirage limité à 25 exemplaires.', '40×60 cm — papier baryté', '149.00', 25, true, 2],
            ['Quai de Saône', 'Vue minimaliste de Lyon au petit matin, brume légère sur la Saône. Édition ouverte.', '20×30 cm — papier satiné', '49.00', -1, false, 3],
            ['Mains d\'enfant', 'Détail tendre d\'une séance grossesse. Noir et blanc argentique.', '30×30 cm — papier mat carré', '69.00', 12, false, 4],
            ['Coulisses de scène', 'Photographie d\'événement, lumière naturelle. Tirage signé, certificat d\'authenticité fourni.', '50×75 cm — papier fine art', '199.00', 5, true, 5],
            ['Carnet de voyage — Marseille', 'Triptyque de photos du Vieux-Port (livré en 3 tirages 20×30 cm).', '3× 20×30 cm — papier baryté', '129.00', 10, false, 6],
        ];
        foreach ($productsData as [$title, $description, $format, $price, $stock, $featured, $position]) {
            $p = new Product();
            $p->setTitle($title);
            $p->setDescription($description);
            $p->setFormat($format);
            $p->setPrice($price);
            $p->setStock($stock);
            $p->setFeatured($featured);
            $p->setPosition($position);
            $p->setPublished(true);
            $manager->persist($p);
        }

        // --- Coupons promo -----------------------------------------------
        $couponsData = [
            ['BIENVENUE10', Coupon::TYPE_PERCENT, '10', null, null, '+6 months'],
            ['NOEL2024', Coupon::TYPE_PERCENT, '20', '100', 100, '+3 months'],
            ['LIVRAISONOFFERTE', Coupon::TYPE_FIXED, '8', '50', null, '+1 year'],
        ];
        foreach ($couponsData as [$code, $type, $value, $minAmount, $maxUses, $validUntilStr]) {
            $c = new Coupon();
            $c->setCode($code);
            $c->setType($type);
            $c->setValue($value);
            if ($minAmount !== null) {
                $c->setMinAmount($minAmount);
            }
            if ($maxUses !== null) {
                $c->setMaxUses($maxUses);
            }
            $c->setValidUntil(new \DateTimeImmutable($validUntilStr));
            $c->setActive(true);
            $manager->persist($c);
        }

        // --- Sample contact request --------------------------------------
        $contact = new ContactRequest();
        $contact->setFullName('Sophie Martin');
        $contact->setEmail('sophie.martin@example.fr');
        $contact->setPhone('06 12 34 56 78');
        $contact->setProjectType('mariage');
        $contact->setEventDate(new \DateTimeImmutable('+4 months'));
        $contact->setMessage('Bonjour, nous nous marions en septembre à Lyon et adorons votre style. Pouvez-vous nous envoyer vos disponibilités et tarifs ? Merci.');
        $manager->persist($contact);

        // --- Blog: categories ---------------------------------------------
        $blogCategoriesData = [
            ['Conseils mariage', 'Préparer son shooting de mariage : tout ce qu\'il faut savoir.', 1],
            ['Coulisses', 'Backstage des séances, choix créatifs et matériel.', 2],
            ['Inspirations', 'Tendances visuelles, lieux à découvrir.', 3],
        ];
        $blogCats = [];
        foreach ($blogCategoriesData as [$n, $d, $p]) {
            $bc = new ArticleCategory();
            $bc->setName($n);
            $bc->setDescription($d);
            $bc->setPosition($p);
            $manager->persist($bc);
            $blogCats[] = $bc;
        }

        // --- Blog: articles -----------------------------------------------
        $articlesData = [
            [
                'title' => '10 conseils pour réussir vos photos de mariage',
                'category' => 0,
                'excerpt' => 'Le mariage est le jour le plus mémorable d\'une vie. Voici mes 10 conseils, accumulés en 10 ans, pour que vos photos racontent vraiment votre histoire — au-delà des poses figées.',
                'content' => "## 1. Choisir un photographe dont le style vous parle\n\nNe choisissez pas un photographe parce qu'il est *populaire*. Choisissez-le parce que **ses photos vous touchent**. Regardez plusieurs reportages complets, pas juste les 5 meilleures images.\n\n## 2. Prévoir un \"first look\" en amont\n\nLe first look — moment privé où les mariés se découvrent avant la cérémonie — détend tout le monde et libère du temps pour les photos de couple. C'est devenu mon moment préféré.\n\n## 3. Préparer un planning photo réaliste\n\nUn cocktail dure rarement 1h30 comme prévu. Comptez 20 minutes de marge entre chaque temps fort.\n\n## 4. Garder du temps pour les photos de couple\n\nMinimum 45 minutes en fin de journée, à la *golden hour*. C'est le moment où la lumière est magique.\n\n## 5. Les détails comptent\n\n- L'alliance posée sur l'invitation\n- Les chaussures avant qu'on les mette\n- Le bouquet sur le rebord d'une fenêtre\n\nCes images racontent autant que les portraits.\n\n## 6. Faire confiance à votre photographe\n\nVous l'avez choisi pour son œil. Laissez-le faire ses propositions sans micro-management. Vous verrez le résultat dans 4 à 6 semaines.\n\n## 7. Préparer une liste de photos famille\n\nUne liste de 8-10 groupes maximum. Au-delà, ça devient interminable et personne ne profite.\n\n## 8. Ne pas négliger la danse\n\nLes photos de soirée racontent l'ambiance. Demandez explicitement à votre photographe de rester jusqu'au bout (ou au moins jusqu'aux premières grosses danses).\n\n## 9. Prévoir un \"plan B\" météo\n\nUne grange, un porche, une salle avec belles fenêtres. La pluie ne doit pas ruiner vos photos.\n\n## 10. Profiter\n\nLa meilleure photo est celle d'un visage détendu et heureux. Si vous vivez votre journée pleinement, le résultat sera magique.\n\n---\n\n**Envie d'en discuter ?** [Contactez-moi](/contact) pour parler de votre projet.",
                'meta_title' => '10 conseils pour réussir ses photos de mariage — Lyon',
                'meta_desc' => 'Photographe pro à Lyon partage 10 conseils essentiels pour avoir des photos de mariage qui racontent votre histoire.',
                'days_ago' => 7,
            ],
            [
                'title' => 'La lumière naturelle : mon outil n°1',
                'category' => 1,
                'excerpt' => 'Pourquoi je travaille presque exclusivement en lumière naturelle, et comment je m\'adapte aux ciels gris.',
                'content' => "Quand on me demande quel est mon **objectif favori**, je réponds toujours : *aucun. C'est la lumière qui compte.*\n\n## Pourquoi la lumière naturelle ?\n\nElle est honnête. Elle ne déforme pas les visages, elle ne crée pas d'ambiance artificielle. Une fenêtre vaut souvent mieux qu'un kit de 3 flashes.\n\n## Mais que faire un jour de pluie ?\n\nLes meilleurs portraits que j'ai faits l'année dernière étaient sous un ciel gris, à 14h, dans un studio improvisé près d'une grande baie vitrée.\n\n> La lumière douce d'un jour couvert est l'équivalent gratuit d'une softbox de 3 mètres.\n\n## Mes 3 règles\n\n1. **Toujours observer la lumière 5 minutes avant de shooter.**\n2. **Placer le sujet par rapport à la fenêtre, pas par rapport au décor.**\n3. **Préférer 16h en hiver qu'à 12h en plein été.**\n\nC'est tout. Le reste, c'est de la pratique.",
                'meta_title' => 'Pourquoi je photographie en lumière naturelle',
                'meta_desc' => 'Coulisses : ma philosophie de la lumière en photographie, et comment je gère les jours gris.',
                'days_ago' => 14,
            ],
            [
                'title' => '5 lieux secrets à Lyon pour vos séances couple',
                'category' => 2,
                'excerpt' => 'Bien au-delà de la Place Bellecour ou des berges du Rhône, voici 5 spots méconnus où la magie opère.',
                'content' => "Lyon regorge de coins méconnus, parfaits pour des photos qui sortent de l'ordinaire.\n\n## 1. La Cour des Voraces (Croix-Rousse)\n\nUne **traboule iconique**, mais photographiée correctement, elle devient un décor magnifique. Préférez 9h le dimanche pour éviter les passants.\n\n## 2. Le Parc des Hauteurs (Fourvière)\n\nVue panoramique sur la ville, lumière dorée garantie le soir. Évitez le belvédère bondé, descendez les escaliers.\n\n## 3. La passerelle du Palais de Justice\n\nLignes minimalistes, fond neutre, idéal pour un style éditorial.\n\n## 4. Le marché de la Croix-Rousse (mardi matin)\n\nVie, couleurs, rires. Si vous aimez les photos vivantes, c'est un terrain de jeu.\n\n## 5. Le parc de Gerland\n\nMoins touristique que la Tête d'Or, plus moderne. Parfait pour les couples qui aiment l'architecture contemporaine.",
                'meta_title' => '5 lieux secrets à Lyon pour photos de couple',
                'meta_desc' => 'Photographe lyonnais : ma sélection de 5 lieux méconnus pour des séances couple originales.',
                'days_ago' => 21,
            ],
            [
                'title' => 'Comment choisir sa tenue pour une séance portrait',
                'category' => 0,
                'excerpt' => 'Les couleurs unies, les matières qui photographient bien, les motifs à éviter : un guide pratique pour préparer votre garde-robe.',
                'content' => "## Les bases\n\n- **Couleurs unies > motifs** : un motif géométrique peut créer un moiré désagréable.\n- **Matières naturelles** : lin, laine, coton mat. Évitez le synthétique brillant.\n- **Tenues coordonnées, pas identiques** : pour un couple, choisissez une palette commune (terre, bleu nuit, écru) sans porter exactement la même chose.\n\n## Ce qu'il faut éviter\n\n- Les logos visibles (datent immédiatement la photo)\n- Le blanc pur sous une lumière intense (crame facilement)\n- Le noir total (mange les détails)\n\n## Mon conseil\n\nApportez **3 tenues maximum**, on choisira ensemble en arrivant en fonction de la lumière et du lieu.",
                'meta_title' => 'Comment choisir sa tenue pour une séance photo portrait',
                'meta_desc' => 'Guide pratique : couleurs, matières, motifs — comment s\'habiller pour des photos réussies.',
                'days_ago' => 30,
            ],
            [
                'title' => 'Behind the scenes : 12 heures avec Sophie & Julien',
                'category' => 1,
                'excerpt' => 'Récit d\'une journée de mariage racontée minute par minute, du matin aux préparatifs jusqu\'à la dernière danse.',
                'content' => "## 8h00 — Les préparatifs\n\nJ'arrive chez Sophie. Sa sœur l'aide à se préparer. La lumière du matin traverse les rideaux, c'est parfait.\n\n## 12h30 — First look\n\nJulien attend dans le jardin, dos tourné. Sophie s'approche. Il se retourne. *Click.* C'est probablement la photo la plus émouvante de la journée.\n\n## 15h00 — La cérémonie\n\nCérémonie laïque. Le témoin pleure. Les invités rient. Je me déplace en silence.\n\n## 19h30 — La golden hour\n\n45 minutes rien que pour eux. Lumière dorée. C'est ici que naissent les portraits qui iront sur le mur du salon.\n\n## 23h00 — La piste\n\nGrand-mère dans le slow. Mariés enlacés. Confettis. C'est la fin officielle du contrat. Je reste encore une heure.\n\n## Ce que je retiens\n\nUn mariage n'est pas une liste de photos à cocher. C'est une histoire à raconter.",
                'meta_title' => 'Reportage mariage : récit d\'une journée complète',
                'meta_desc' => 'Behind the scenes : 12 heures dans la peau d\'un photographe de mariage, du matin à la nuit.',
                'days_ago' => 45,
            ],
        ];

        foreach ($articlesData as $data) {
            $a = new Article();
            $a->setTitle($data['title']);
            $a->setCategory($blogCats[$data['category']]);
            $a->setExcerpt($data['excerpt']);
            $a->setContent($data['content']);
            $a->setMetaTitle($data['meta_title']);
            $a->setMetaDescription($data['meta_desc']);
            $a->setAuthor($admin);
            $a->setPublished(true);
            $a->setPublishedAt(new \DateTimeImmutable('-'.$data['days_ago'].' days'));
            $a->setViewCount(random_int(40, 800));
            $manager->persist($a);
        }

        // --- Newsletter subscribers --------------------------------------
        foreach (['marie.test@example.fr', 'paul.dupont@example.fr', 'lea.client@example.fr'] as $email) {
            $sub = new NewsletterSubscriber();
            $sub->setEmail($email);
            $manager->persist($sub);
        }

        // --- Demo client gallery -----------------------------------------
        $this->createDemoClientGallery($manager);

        $manager->flush();
    }

    private function createDemoClientGallery(ObjectManager $manager): void
    {
        $gallery = new ClientGallery();
        $gallery->setTitle('Mariage Sophie & Julien — Démo');
        $gallery->setClientName('Sophie & Julien');
        $gallery->setClientEmail('sophie.demo@example.fr');
        $gallery->setShootDate(new \DateTimeImmutable('-2 months'));
        $gallery->setExpiresAt((new \DateTimeImmutable())->modify('+6 months'));
        $gallery->setWelcomeMessage("Bonjour Sophie & Julien,\n\nUn immense merci pour cette journée magnifique. Voici votre galerie privée — prenez le temps de la regarder, téléchargez ce qui vous parle. Belle découverte !");
        $gallery->setAllowDownload(true);
        $gallery->setActive(true);

        $hasher = $this->hasherFactory->getPasswordHasher('common');
        $gallery->setPasswordHash($hasher->hash('demo1234'));

        // Generate placeholder image files
        if (!is_dir($this->clientGalleriesDir)) {
            @mkdir($this->clientGalleriesDir, 0775, true);
        }

        for ($i = 1; $i <= 6; $i++) {
            $filename = sprintf('demo-%d-%s.jpg', $i, bin2hex(random_bytes(4)));
            $path = $this->clientGalleriesDir.DIRECTORY_SEPARATOR.$filename;

            $url = "https://picsum.photos/seed/clientgal{$i}/1600/1100";
            $content = @file_get_contents($url);
            if ($content !== false) {
                file_put_contents($path, $content);
            } else {
                // Network unavailable — write a minimal valid 1x1 jpeg so tests still pass.
                file_put_contents($path, base64_decode(
                    '/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDABALDA4MChAODQ4SERATGCgaGBYWGDEjJR0oOjM9PDkzODdASFxOQERXRTc4UG1RV19iZ2hnPk1xeXBkeFxlZ2P/2wBDARESEhgVGC8aGi9jQjhCY2NjY2NjY2NjY2NjY2NjY2NjY2NjY2NjY2NjY2NjY2NjY2NjY2NjY2NjY2NjY2NjY2P/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFAEBAAAAAAAAAAAAAAAAAAAAAP/EABQRAQAAAAAAAAAAAAAAAAAAAAD/2gAMAwEAAhEDEQA/AKpAB//Z'
                ));
            }

            $photo = new ClientPhoto();
            $photo->setImageName($filename);
            $photo->setOriginalName(sprintf('mariage-sophie-julien-%02d.jpg', $i));
            $photo->setImageSize(filesize($path) ?: 1024);
            $photo->setPosition($i);
            $photo->setTitle(sprintf('Photo %d', $i));

            $gallery->addPhoto($photo);
            $manager->persist($photo);
        }

        $manager->persist($gallery);
    }
}
