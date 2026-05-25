// Slide deck de pitch commercial pour Evan Orofino
// Génère pitch-deck.pptx à la racine du projet studio-photo

const pptxgen = require("C:/Users/evano/AppData/Roaming/npm/node_modules/pptxgenjs");

const pres = new pptxgen();
pres.layout = "LAYOUT_WIDE"; // 13.3 × 7.5"
pres.author = "Evan Orofino";
pres.title = "Studio Photo — Pitch commercial";
pres.subject = "Plateforme web métier pour photographe professionnel";

// Palette
const INK = "1A1A1A";        // Noir profond
const PAPER = "FFFFFF";      // Blanc
const PAPER_ALT = "F8F6F1";  // Crème très léger pour cards
const GOLD = "C8A97E";       // Accent
const MUTED = "6B6B6B";      // Gris légende
const SUCCESS = "198754";    // Vert pour ✓

const TITLE_FONT = "Georgia";
const BODY_FONT = "Calibri";

// Helpers
const W = 13.3, H = 7.5;

function addFooter(slide, pageNum, total) {
    slide.addText("Studio Photo • Pitch commercial • Evan Orofino", {
        x: 0.6, y: H - 0.4, w: 8, h: 0.3,
        fontSize: 9, color: MUTED, fontFace: BODY_FONT,
    });
    slide.addText(`${pageNum} / ${total}`, {
        x: W - 1.4, y: H - 0.4, w: 0.8, h: 0.3,
        fontSize: 9, color: MUTED, fontFace: BODY_FONT, align: "right",
    });
}

// ────────────────────────────────────────────────────────────────
// SLIDE 1 — Title (full bleed dark)
// ────────────────────────────────────────────────────────────────
{
    const s = pres.addSlide();
    s.background = { color: INK };

    // Small gold tag
    s.addText("STUDIO PHOTO  •  PLATEFORME MÉTIER", {
        x: 0.8, y: 1.6, w: 8, h: 0.35,
        fontSize: 11, color: GOLD, fontFace: BODY_FONT, charSpacing: 8, bold: true,
    });

    // Big title
    s.addText("Vendez plus.\nLivrez mieux.\nGardez vos clients.", {
        x: 0.8, y: 2.2, w: 11, h: 3.0,
        fontSize: 64, color: PAPER, fontFace: TITLE_FONT, bold: true,
        valign: "top",
    });

    // Sub-tagline
    s.addText("Un site complet pour photographe pro, branché à Stripe, prêt en 48h.", {
        x: 0.8, y: 5.4, w: 11, h: 0.5,
        fontSize: 18, color: "CCCCCC", fontFace: BODY_FONT, italic: true,
    });

    // Gold underline accent
    s.addShape(pres.shapes.RECTANGLE, {
        x: 0.8, y: 6.0, w: 1.5, h: 0.05,
        fill: { color: GOLD }, line: { color: GOLD, width: 0 },
    });

    s.addText("Evan Orofino — Développeur Symfony freelance, Lyon", {
        x: 0.8, y: 6.2, w: 9, h: 0.4,
        fontSize: 13, color: PAPER, fontFace: BODY_FONT,
    });
}

// ────────────────────────────────────────────────────────────────
// SLIDE 2 — Le constat (3 grandes stats)
// ────────────────────────────────────────────────────────────────
{
    const s = pres.addSlide();
    s.background = { color: PAPER };

    s.addText("LE CONSTAT", {
        x: 0.6, y: 0.55, w: 6, h: 0.35,
        fontSize: 11, color: GOLD, fontFace: BODY_FONT, charSpacing: 8, bold: true,
    });
    s.addText("Le marché des sites de photographes est bloqué dans le passé.", {
        x: 0.6, y: 0.95, w: 12, h: 0.8,
        fontSize: 30, color: INK, fontFace: TITLE_FONT, bold: true,
    });

    // 3 colonnes de stats (NBSP pour éviter les wraps moches)
    const stats = [
        ["70 %", "des sites de photographes pro sont des WordPress avec galerie statique — aucune vraie automatisation."],
        ["3 h+", "passées chaque semaine par le photographe à faire de la facturation, du suivi et de la livraison manuelle."],
        ["−40 %", "de chiffre d'affaires potentiel perdu : pas de boutique tirages, pas de réservation directe en ligne."],
    ];

    const cardW = 3.9, cardH = 3.4, gap = 0.3;
    const startX = (W - (3 * cardW + 2 * gap)) / 2;

    stats.forEach(([num, desc], i) => {
        const x = startX + i * (cardW + gap);
        const y = 2.6;

        // Card background
        s.addShape(pres.shapes.RECTANGLE, {
            x, y, w: cardW, h: cardH,
            fill: { color: PAPER_ALT }, line: { color: GOLD, width: 0 },
        });

        // Vertical gold accent on left
        s.addShape(pres.shapes.RECTANGLE, {
            x, y, w: 0.08, h: cardH,
            fill: { color: GOLD }, line: { color: GOLD, width: 0 },
        });

        // Big number (centered both ways, smaller font to avoid wrapping)
        s.addText(num, {
            x: x + 0.3, y: y + 0.5, w: cardW - 0.6, h: 1.4,
            fontSize: 60, color: INK, fontFace: TITLE_FONT, bold: true,
            align: "center", valign: "middle",
        });

        // Description (centered)
        s.addText(desc, {
            x: x + 0.4, y: y + 2.1, w: cardW - 0.8, h: cardH - 2.3,
            fontSize: 13, color: MUTED, fontFace: BODY_FONT,
            align: "center", valign: "top",
        });
    });

    s.addText("→ Le photographe a besoin d'un outil qui fait grandir son business, pas d'un catalogue figé.", {
        x: 0.6, y: 6.3, w: 12, h: 0.5,
        fontSize: 16, color: INK, fontFace: BODY_FONT, italic: true, align: "center",
    });

    addFooter(s, 2, 11);
}

// ────────────────────────────────────────────────────────────────
// SLIDE 3 — Notre solution
// ────────────────────────────────────────────────────────────────
{
    const s = pres.addSlide();
    s.background = { color: PAPER };

    s.addText("LA SOLUTION", {
        x: 0.6, y: 0.55, w: 6, h: 0.35,
        fontSize: 11, color: GOLD, fontFace: BODY_FONT, charSpacing: 8, bold: true,
    });
    s.addText("Studio Photo : votre business, en une seule plateforme.", {
        x: 0.6, y: 0.95, w: 12, h: 0.8,
        fontSize: 30, color: INK, fontFace: TITLE_FONT, bold: true,
    });

    s.addText("Une seule installation, sous votre nom de domaine, qui couvre tout votre cycle de vente — du premier visiteur jusqu'à la livraison des photos finales.", {
        x: 0.6, y: 1.95, w: 12, h: 0.8,
        fontSize: 15, color: MUTED, fontFace: BODY_FONT,
    });

    // Workflow horizontal : 5 étapes
    const steps = [
        ["1", "Vitrine", "Galerie + blog + témoignages SEO-optimisés"],
        ["2", "Devis", "Formulaire intelligent + email automatique"],
        ["3", "Réservation", "Acompte CB Stripe + calendrier dispo"],
        ["4", "Livraison", "Galerie privée client + ZIP téléchargeable"],
        ["5", "Vente++", "Boutique tirages + codes promo"],
    ];

    const stepW = 2.3, stepGap = 0.15;
    const stepsTotalW = 5 * stepW + 4 * stepGap;
    const stepsStartX = (W - stepsTotalW) / 2;
    const stepsY = 3.4;

    steps.forEach(([num, title, desc], i) => {
        const x = stepsStartX + i * (stepW + stepGap);

        // Numéroté dans un cercle gold
        s.addShape(pres.shapes.OVAL, {
            x: x + (stepW - 0.7) / 2, y: stepsY, w: 0.7, h: 0.7,
            fill: { color: GOLD }, line: { color: GOLD, width: 0 },
        });
        s.addText(num, {
            x: x + (stepW - 0.7) / 2, y: stepsY, w: 0.7, h: 0.7,
            fontSize: 24, color: PAPER, fontFace: TITLE_FONT, bold: true,
            align: "center", valign: "middle",
        });

        // Title
        s.addText(title, {
            x, y: stepsY + 0.9, w: stepW, h: 0.5,
            fontSize: 18, color: INK, fontFace: TITLE_FONT, bold: true,
            align: "center",
        });

        // Description
        s.addText(desc, {
            x: x + 0.1, y: stepsY + 1.45, w: stepW - 0.2, h: 1.2,
            fontSize: 11, color: MUTED, fontFace: BODY_FONT,
            align: "center", valign: "top",
        });

        // Arrow between
        if (i < steps.length - 1) {
            s.addShape(pres.shapes.LINE, {
                x: x + stepW - 0.05, y: stepsY + 0.35, w: stepGap + 0.1, h: 0,
                line: { color: GOLD, width: 1.5 },
            });
        }
    });

    s.addText("Une plateforme, zéro friction, plus de revenus.", {
        x: 0.6, y: 6.4, w: 12, h: 0.5,
        fontSize: 17, color: INK, fontFace: BODY_FONT, italic: true, bold: true, align: "center",
    });

    addFooter(s, 3, 11);
}

// ────────────────────────────────────────────────────────────────
// SLIDE 4 — Démo : ce que voit votre client
// ────────────────────────────────────────────────────────────────
{
    const s = pres.addSlide();
    s.background = { color: PAPER };

    s.addText("L'EXPÉRIENCE CLIENT", {
        x: 0.6, y: 0.55, w: 6, h: 0.35,
        fontSize: 11, color: GOLD, fontFace: BODY_FONT, charSpacing: 8, bold: true,
    });
    s.addText("Ce que voit votre client, à chaque étape.", {
        x: 0.6, y: 0.95, w: 12, h: 0.8,
        fontSize: 30, color: INK, fontFace: TITLE_FONT, bold: true,
    });

    // 4 quadrants : prospect, réservation, livraison, fidélisation
    const phases = [
        ["Avant la séance", "Découvre votre travail via votre galerie, lit vos avis, voit vos disponibilités en temps réel. Réserve sa date en 3 minutes avec acompte CB.", GOLD],
        ["Pendant la séance", "Vous êtes serein : la date est verrouillée, l'acompte est encaissé, les attentes du client sont notées dans son dossier.", INK],
        ["Après la séance", "Reçoit un lien privé + mot de passe. Télécharge ses photos en HD individuellement ou en ZIP. Tout en lumière naturelle, design pro.", GOLD],
        ["Plus tard", "Achète un tirage d'art sur votre boutique. Reçoit votre newsletter. Vous recommande à ses amis avec un témoignage que vous publiez en 1 clic.", INK],
    ];

    const qW = 5.8, qH = 2.3, qGap = 0.3;
    const qStartX = (W - (2 * qW + qGap)) / 2;
    const qStartY = 2.2;

    phases.forEach(([title, body, color], i) => {
        const col = i % 2;
        const row = Math.floor(i / 2);
        const x = qStartX + col * (qW + qGap);
        const y = qStartY + row * (qH + qGap);

        // Card
        s.addShape(pres.shapes.RECTANGLE, {
            x, y, w: qW, h: qH,
            fill: { color: PAPER_ALT }, line: { color: "E5E0D5", width: 1 },
        });

        // Title bar
        s.addShape(pres.shapes.RECTANGLE, {
            x, y, w: qW, h: 0.55,
            fill: { color }, line: { color, width: 0 },
        });
        s.addText(title, {
            x: x + 0.3, y, w: qW - 0.6, h: 0.55,
            fontSize: 15, color: PAPER, fontFace: TITLE_FONT, bold: true,
            valign: "middle",
        });

        // Body
        s.addText(body, {
            x: x + 0.3, y: y + 0.75, w: qW - 0.6, h: qH - 0.9,
            fontSize: 12.5, color: INK, fontFace: BODY_FONT,
            valign: "top",
        });
    });

    addFooter(s, 4, 11);
}

// ────────────────────────────────────────────────────────────────
// SLIDE 5 — Tout ce que vous gagnez (features grid)
// ────────────────────────────────────────────────────────────────
{
    const s = pres.addSlide();
    s.background = { color: PAPER };

    s.addText("CE QUI EST INCLUS", {
        x: 0.6, y: 0.55, w: 6, h: 0.35,
        fontSize: 11, color: GOLD, fontFace: BODY_FONT, charSpacing: 8, bold: true,
    });
    s.addText("Tout est livré clé en main. Pas d'add-on caché.", {
        x: 0.6, y: 0.95, w: 12, h: 0.8,
        fontSize: 30, color: INK, fontFace: TITLE_FONT, bold: true,
    });

    const features = [
        ["Site vitrine", "Accueil, galerie filtrable, prestations, témoignages, blog, à propos, FAQ, contact."],
        ["Galerie privée client", "Token + mot de passe, expiration auto, photos hors web public, téléchargement individuel ou ZIP."],
        ["Réservation en ligne", "Calendrier des dispos, formulaire avec acompte 30% via Stripe Checkout."],
        ["Boutique tirages", "Catalogue + panier session + checkout Stripe + codes promo + gestion stock."],
        ["Dashboard analytics", "8 KPI + 6 graphiques Chart.js : CA, conversions, photos vues, demandes par type."],
        ["Blog SEO + RSS", "Éditeur Markdown, catégories, JSON-LD, flux RSS pour fidéliser et capter Google."],
        ["Conformité RGPD", "Mentions légales, politique de confidentialité, bandeau cookies, droits utilisateurs."],
        ["Multi-langue", "Site disponible en FR / EN, switcher dans la navigation."],
        ["Mode sombre + WebP", "Design adaptatif, images optimisées (WebP automatique, srcset Retina)."],
    ];

    const fCols = 3, fRows = 3;
    const fW = 4.0, fH = 1.45, fGap = 0.15;
    const fStartX = (W - (fCols * fW + (fCols - 1) * fGap)) / 2;
    const fStartY = 2.0;

    features.forEach(([title, body], i) => {
        const col = i % fCols;
        const row = Math.floor(i / fCols);
        const x = fStartX + col * (fW + fGap);
        const y = fStartY + row * (fH + fGap);

        // Check icon (gold circle with ✓)
        s.addShape(pres.shapes.OVAL, {
            x, y: y + 0.12, w: 0.45, h: 0.45,
            fill: { color: GOLD }, line: { color: GOLD, width: 0 },
        });
        s.addText("✓", {
            x, y: y + 0.12, w: 0.45, h: 0.45,
            fontSize: 16, color: PAPER, fontFace: TITLE_FONT, bold: true,
            align: "center", valign: "middle",
        });

        // Title
        s.addText(title, {
            x: x + 0.6, y: y + 0.05, w: fW - 0.6, h: 0.45,
            fontSize: 14, color: INK, fontFace: TITLE_FONT, bold: true,
            valign: "middle", margin: 0,
        });

        // Body
        s.addText(body, {
            x: x + 0.6, y: y + 0.55, w: fW - 0.6, h: fH - 0.6,
            fontSize: 10.5, color: MUTED, fontFace: BODY_FONT,
            valign: "top",
        });
    });

    addFooter(s, 5, 11);
}

// ────────────────────────────────────────────────────────────────
// SLIDE 6 — Tech & sécurité
// ────────────────────────────────────────────────────────────────
{
    const s = pres.addSlide();
    s.background = { color: PAPER };

    s.addText("ARCHITECTURE & SÉCURITÉ", {
        x: 0.6, y: 0.55, w: 8, h: 0.35,
        fontSize: 11, color: GOLD, fontFace: BODY_FONT, charSpacing: 8, bold: true,
    });
    s.addText("Construit aux standards des startups françaises.", {
        x: 0.6, y: 0.95, w: 12, h: 0.8,
        fontSize: 30, color: INK, fontFace: TITLE_FONT, bold: true,
    });

    // Left: 4 tech items
    const techItems = [
        ["Symfony 7.4", "Framework français reconnu (Audi, Spotify, Drupal). Maintenu 5 ans en LTS."],
        ["49 tests automatisés", "Une suite PHPUnit qui valide chaque fonctionnalité. Le site ne casse pas en silence."],
        ["CI/CD GitHub Actions", "Chaque modification est testée automatiquement avant déploiement."],
        ["Stripe + RGPD", "Paiement certifié niveau bancaire. Conformité européenne validée."],
    ];

    techItems.forEach(([title, body], i) => {
        const y = 2.2 + i * 1.1;

        s.addText(title, {
            x: 0.7, y, w: 6.5, h: 0.4,
            fontSize: 18, color: INK, fontFace: TITLE_FONT, bold: true,
        });
        s.addText(body, {
            x: 0.7, y: y + 0.42, w: 6.5, h: 0.6,
            fontSize: 12, color: MUTED, fontFace: BODY_FONT,
        });

        // Small gold tick
        s.addShape(pres.shapes.RECTANGLE, {
            x: 0.5, y: y + 0.1, w: 0.04, h: 0.6,
            fill: { color: GOLD }, line: { color: GOLD, width: 0 },
        });
    });

    // Right side: dark callout card
    s.addShape(pres.shapes.RECTANGLE, {
        x: 7.8, y: 2.2, w: 5.0, h: 4.4,
        fill: { color: INK }, line: { color: INK, width: 0 },
    });

    s.addText("CODE PUBLIC", {
        x: 8.1, y: 2.4, w: 4.4, h: 0.35,
        fontSize: 10, color: GOLD, fontFace: BODY_FONT, charSpacing: 6, bold: true,
    });
    s.addText("Vous voulez vérifier ?", {
        x: 8.1, y: 2.8, w: 4.4, h: 0.6,
        fontSize: 22, color: PAPER, fontFace: TITLE_FONT, bold: true,
    });

    s.addText("L'intégralité du code source est consultable publiquement sur GitHub. Aucun secret, aucune boîte noire.", {
        x: 8.1, y: 3.7, w: 4.4, h: 1.2,
        fontSize: 13, color: "CCCCCC", fontFace: BODY_FONT, italic: true,
    });

    s.addText("github.com/EvanOROFINO/studio-photo", {
        x: 8.1, y: 5.4, w: 4.4, h: 0.4,
        fontSize: 14, color: GOLD, fontFace: BODY_FONT, bold: true,
    });

    s.addText("✓ 49 tests verts  •  ✓ CI passing  •  ✓ Dependabot actif", {
        x: 8.1, y: 5.95, w: 4.4, h: 0.4,
        fontSize: 11, color: PAPER, fontFace: BODY_FONT,
    });

    addFooter(s, 6, 11);
}

// ────────────────────────────────────────────────────────────────
// SLIDE 7 — ROI / Combien ça vous rapporte
// ────────────────────────────────────────────────────────────────
{
    const s = pres.addSlide();
    s.background = { color: PAPER };

    s.addText("RETOUR SUR INVESTISSEMENT", {
        x: 0.6, y: 0.55, w: 8, h: 0.35,
        fontSize: 11, color: GOLD, fontFace: BODY_FONT, charSpacing: 8, bold: true,
    });
    s.addText("Combien ça vous rapporte, en clair.", {
        x: 0.6, y: 0.95, w: 12, h: 0.8,
        fontSize: 30, color: INK, fontFace: TITLE_FONT, bold: true,
    });

    // 3 cards horizontales avec gros chiffres (labels propres, pas d'espaces qui wrappent)
    const roi = [
        ["+3", "séances / mois", "supplémentaires grâce à la réservation directe en ligne (au lieu d'aller-retours email)."],
        ["+450€", "par mois", "estimés via la boutique tirages (5 tirages vendus à 90€ de marge moyenne)."],
        ["−2h", "par semaine", "économisées sur la facturation et la livraison manuelle (Stripe + galerie auto)."],
    ];

    const rW = 4.0, rH = 4.0, rGap = 0.3;
    const rStartX = (W - (3 * rW + 2 * rGap)) / 2;
    const rY = 2.3;

    roi.forEach(([big, sub, body], i) => {
        const x = rStartX + i * (rW + rGap);

        s.addShape(pres.shapes.RECTANGLE, {
            x, y: rY, w: rW, h: rH,
            fill: { color: i === 1 ? INK : PAPER_ALT },
            line: { color: i === 1 ? INK : "E5E0D5", width: 1 },
        });

        s.addText(big, {
            x: x + 0.3, y: rY + 0.7, w: rW - 0.6, h: 1.5,
            fontSize: 64, color: i === 1 ? GOLD : INK, fontFace: TITLE_FONT, bold: true,
            align: "center", valign: "middle",
        });

        s.addText(sub, {
            x: x + 0.3, y: rY + 2.25, w: rW - 0.6, h: 0.4,
            fontSize: 14, color: i === 1 ? PAPER : MUTED, fontFace: BODY_FONT,
            align: "center", italic: true,
        });

        s.addText(body, {
            x: x + 0.4, y: rY + 2.7, w: rW - 0.8, h: rH - 2.9,
            fontSize: 12, color: i === 1 ? "DDDDDD" : MUTED, fontFace: BODY_FONT,
            align: "center", valign: "top",
        });
    });

    s.addText("Avec un panier moyen mariage à 1 500 €, la plateforme s'auto-finance en 2 séances.", {
        x: 0.6, y: 6.6, w: 12, h: 0.4,
        fontSize: 14, color: INK, fontFace: BODY_FONT, italic: true, align: "center",
    });

    addFooter(s, 7, 11);
}

// ────────────────────────────────────────────────────────────────
// SLIDE 8 — Le prix (slide noire pleine)
// ────────────────────────────────────────────────────────────────
{
    const s = pres.addSlide();
    s.background = { color: INK };

    s.addText("UN SEUL PACK. TOUT INCLUS.", {
        x: 0.8, y: 0.7, w: 10, h: 0.4,
        fontSize: 11, color: GOLD, fontFace: BODY_FONT, charSpacing: 8, bold: true,
    });

    // Pricing card central
    s.addShape(pres.shapes.RECTANGLE, {
        x: 1.8, y: 1.6, w: 9.7, h: 5.0,
        fill: { color: PAPER }, line: { color: GOLD, width: 2 },
    });

    s.addText("Pack Studio Photo", {
        x: 2.2, y: 1.9, w: 8.9, h: 0.6,
        fontSize: 26, color: INK, fontFace: TITLE_FONT, bold: true,
    });
    s.addText("Site complet, déployé sous votre domaine, formé en 1 demi-journée.", {
        x: 2.2, y: 2.5, w: 8.9, h: 0.4,
        fontSize: 14, color: MUTED, fontFace: BODY_FONT, italic: true,
    });

    // Big price (NBSP between digits and € to keep it on one line)
    s.addText("3 000 €", {
        x: 2.2, y: 3.2, w: 5.5, h: 1.6,
        fontSize: 80, color: INK, fontFace: TITLE_FONT, bold: true,
        valign: "middle",
    });
    s.addText("TTC, payable en 2 fois sans frais", {
        x: 2.2, y: 4.85, w: 5.5, h: 0.4,
        fontSize: 12, color: MUTED, fontFace: BODY_FONT,
    });

    // Right: included items
    const included = [
        "Développement complet sur mesure",
        "Personnalisation à votre identité",
        "Installation sur votre nom de domaine",
        "Migration de vos photos existantes",
        "Formation 3h sur le back-office",
        "Garantie satisfait ou remboursé 30 jours",
    ];

    s.addText("Inclus :", {
        x: 8.0, y: 3.1, w: 3.3, h: 0.4,
        fontSize: 14, color: INK, fontFace: TITLE_FONT, bold: true,
    });

    included.forEach((item, i) => {
        s.addShape(pres.shapes.OVAL, {
            x: 8.0, y: 3.6 + i * 0.42 + 0.04, w: 0.18, h: 0.18,
            fill: { color: GOLD }, line: { color: GOLD, width: 0 },
        });
        s.addText(item, {
            x: 8.3, y: 3.6 + i * 0.42, w: 3.2, h: 0.3,
            fontSize: 12, color: INK, fontFace: BODY_FONT,
            valign: "middle", margin: 0,
        });
    });

    // Maintenance subline outside the card
    s.addText("+ 50 € / mois : hébergement haute disponibilité, sauvegardes quotidiennes, mises à jour de sécurité.", {
        x: 1.8, y: 6.8, w: 9.7, h: 0.4,
        fontSize: 12, color: PAPER, fontFace: BODY_FONT, italic: true, align: "center",
    });
}

// ────────────────────────────────────────────────────────────────
// SLIDE 9 — Pourquoi moi (Evan)
// ────────────────────────────────────────────────────────────────
{
    const s = pres.addSlide();
    s.background = { color: PAPER };

    s.addText("POURQUOI MOI ?", {
        x: 0.6, y: 0.55, w: 6, h: 0.35,
        fontSize: 11, color: GOLD, fontFace: BODY_FONT, charSpacing: 8, bold: true,
    });
    s.addText("Un freelance local, transparent, qui livre.", {
        x: 0.6, y: 0.95, w: 12, h: 0.8,
        fontSize: 30, color: INK, fontFace: TITLE_FONT, bold: true,
    });

    // Left column: 4 reasons
    const reasons = [
        ["Lyonnais",     "Je travaille avec des photographes de la métropole. RDV physique possible chaque semaine."],
        ["Spécialisé",   "Cette plateforme, c'est MON produit. Je connais chaque ligne du code, je l'améliore en continu."],
        ["Transparent",  "Code open-source sur GitHub, démos en vrai, références consultables. Aucune boîte noire."],
        ["Réactif",      "Réponse sous 24h ouvrées garantie. Vous m'appelez, je décroche."],
    ];

    reasons.forEach(([title, body], i) => {
        const y = 2.2 + i * 1.1;

        s.addShape(pres.shapes.RECTANGLE, {
            x: 0.6, y, w: 0.04, h: 0.95,
            fill: { color: GOLD }, line: { color: GOLD, width: 0 },
        });

        s.addText(title, {
            x: 0.85, y, w: 6.5, h: 0.4,
            fontSize: 18, color: INK, fontFace: TITLE_FONT, bold: true,
        });
        s.addText(body, {
            x: 0.85, y: y + 0.42, w: 6.5, h: 0.5,
            fontSize: 12, color: MUTED, fontFace: BODY_FONT,
        });
    });

    // Right column: profile card
    s.addShape(pres.shapes.RECTANGLE, {
        x: 8.0, y: 2.2, w: 4.8, h: 4.4,
        fill: { color: PAPER_ALT }, line: { color: "E5E0D5", width: 1 },
    });

    // Initiales dans un cercle gold
    s.addShape(pres.shapes.OVAL, {
        x: 8.0 + (4.8 - 1.4) / 2, y: 2.5, w: 1.4, h: 1.4,
        fill: { color: GOLD }, line: { color: GOLD, width: 0 },
    });
    s.addText("EO", {
        x: 8.0 + (4.8 - 1.4) / 2, y: 2.5, w: 1.4, h: 1.4,
        fontSize: 48, color: PAPER, fontFace: TITLE_FONT, bold: true,
        align: "center", valign: "middle",
    });

    s.addText("Evan Orofino", {
        x: 8.0, y: 4.1, w: 4.8, h: 0.5,
        fontSize: 22, color: INK, fontFace: TITLE_FONT, bold: true, align: "center",
    });
    s.addText("Développeur Symfony freelance", {
        x: 8.0, y: 4.65, w: 4.8, h: 0.4,
        fontSize: 13, color: MUTED, fontFace: BODY_FONT, italic: true, align: "center",
    });

    s.addShape(pres.shapes.LINE, {
        x: 9.5, y: 5.2, w: 1.8, h: 0,
        line: { color: GOLD, width: 1 },
    });

    s.addText("Lyon, France", {
        x: 8.0, y: 5.4, w: 4.8, h: 0.35,
        fontSize: 12, color: INK, fontFace: BODY_FONT, align: "center",
    });
    s.addText("evanorofino.pro@gmail.com", {
        x: 8.0, y: 5.75, w: 4.8, h: 0.35,
        fontSize: 11, color: MUTED, fontFace: BODY_FONT, align: "center",
    });
    s.addText("github.com/EvanOROFINO", {
        x: 8.0, y: 6.1, w: 4.8, h: 0.35,
        fontSize: 11, color: MUTED, fontFace: BODY_FONT, align: "center",
    });

    addFooter(s, 9, 11);
}

// ────────────────────────────────────────────────────────────────
// SLIDE 10 — Comment on commence
// ────────────────────────────────────────────────────────────────
{
    const s = pres.addSlide();
    s.background = { color: PAPER };

    s.addText("ET MAINTENANT ?", {
        x: 0.6, y: 0.55, w: 6, h: 0.35,
        fontSize: 11, color: GOLD, fontFace: BODY_FONT, charSpacing: 8, bold: true,
    });
    s.addText("3 étapes simples pour démarrer.", {
        x: 0.6, y: 0.95, w: 12, h: 0.8,
        fontSize: 30, color: INK, fontFace: TITLE_FONT, bold: true,
    });

    const steps = [
        ["Aujourd'hui",  "Vous me dites OK. On signe un devis, vous payez 50% d'acompte."],
        ["Semaine 1",    "Je personnalise la plateforme : votre identité, vos photos, vos prestations, vos tarifs."],
        ["Semaine 2",    "Mise en ligne sur votre domaine, formation 3h, et vous êtes opérationnel."],
    ];

    const sW = 3.9, sH = 4.0, sGap = 0.35;
    const sStartX = (W - (3 * sW + 2 * sGap)) / 2;
    const sY = 2.3;

    steps.forEach(([title, body], i) => {
        const x = sStartX + i * (sW + sGap);

        s.addShape(pres.shapes.RECTANGLE, {
            x, y: sY, w: sW, h: sH,
            fill: { color: i === 1 ? GOLD : PAPER_ALT },
            line: { color: i === 1 ? GOLD : "E5E0D5", width: 1 },
        });

        s.addText(`Étape ${i + 1}`, {
            x: x + 0.4, y: sY + 0.4, w: sW - 0.8, h: 0.35,
            fontSize: 11, color: i === 1 ? PAPER : MUTED, fontFace: BODY_FONT,
            charSpacing: 6, bold: true,
        });
        s.addText(title, {
            x: x + 0.4, y: sY + 0.85, w: sW - 0.8, h: 0.7,
            fontSize: 26, color: i === 1 ? PAPER : INK, fontFace: TITLE_FONT, bold: true,
        });
        s.addText(body, {
            x: x + 0.4, y: sY + 1.95, w: sW - 0.8, h: sH - 2.2,
            fontSize: 13, color: i === 1 ? PAPER : MUTED, fontFace: BODY_FONT,
            valign: "top",
        });
    });

    s.addText("Démonstration en ligne et devis personnalisé sur simple demande.", {
        x: 0.6, y: 6.6, w: 12, h: 0.4,
        fontSize: 14, color: INK, fontFace: BODY_FONT, italic: true, align: "center",
    });

    addFooter(s, 10, 11);
}

// ────────────────────────────────────────────────────────────────
// SLIDE 11 — Closing / coordonnées
// ────────────────────────────────────────────────────────────────
{
    const s = pres.addSlide();
    s.background = { color: INK };

    s.addText("MERCI.", {
        x: 0.8, y: 1.4, w: 11, h: 1.4,
        fontSize: 110, color: PAPER, fontFace: TITLE_FONT, bold: true,
    });

    s.addText("Discutons de votre projet — un appel, sans engagement.", {
        x: 0.8, y: 3.3, w: 11, h: 0.6,
        fontSize: 22, color: "DDDDDD", fontFace: BODY_FONT, italic: true,
    });

    s.addShape(pres.shapes.RECTANGLE, {
        x: 0.8, y: 4.2, w: 1.5, h: 0.05,
        fill: { color: GOLD }, line: { color: GOLD, width: 0 },
    });

    s.addText("Evan Orofino", {
        x: 0.8, y: 4.5, w: 11, h: 0.5,
        fontSize: 22, color: PAPER, fontFace: TITLE_FONT, bold: true,
    });
    s.addText("Développeur Symfony freelance — Lyon", {
        x: 0.8, y: 5.0, w: 11, h: 0.4,
        fontSize: 14, color: GOLD, fontFace: BODY_FONT, italic: true,
    });

    s.addText("evanorofino.pro@gmail.com", {
        x: 0.8, y: 5.7, w: 11, h: 0.4,
        fontSize: 16, color: PAPER, fontFace: BODY_FONT,
    });
    s.addText("github.com/EvanOROFINO/studio-photo", {
        x: 0.8, y: 6.15, w: 11, h: 0.4,
        fontSize: 14, color: "AAAAAA", fontFace: BODY_FONT,
    });
}

// Save
pres.writeFile({ fileName: "C:/Users/evano/Desktop/studio-photo/pitch-deck.pptx" })
    .then(name => console.log("✓ Created: " + name))
    .catch(err => { console.error("✗ Error:", err); process.exit(1); });
