<?php

namespace App\Controller;

use App\Entity\ClientGallery;
use App\Entity\ClientPhoto;
use App\Repository\ClientGalleryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;

class ClientGalleryController extends AbstractController
{
    private const SESSION_PREFIX = 'client_gallery_unlocked_';
    private const SESSION_LIFETIME = 7200; // 2 hours

    public function __construct(
        private readonly ClientGalleryRepository $repository,
        private readonly PasswordHasherFactoryInterface $hasherFactory,
        private readonly EntityManagerInterface $em,
        private readonly string $clientGalleriesDir,
    ) {
    }

    #[Route('/galerie-client/{token}', name: 'app_client_gallery_login', requirements: ['token' => '[a-f0-9]{32}'])]
    public function login(string $token, Request $request): Response
    {
        $gallery = $this->repository->findOneByToken($token);
        if (!$gallery || !$gallery->isAccessible()) {
            return $this->render('client_gallery/unavailable.html.twig', [
                'gallery' => $gallery,
            ], new Response('', 404));
        }

        if ($this->isUnlocked($request, $token)) {
            return $this->redirectToRoute('app_client_gallery_view', ['token' => $token]);
        }

        $error = null;

        if ($request->isMethod('POST')) {
            $submittedPassword = (string) $request->request->get('password', '');
            $csrf = (string) $request->request->get('_token', '');

            if (!$this->isCsrfTokenValid('client_gallery_login', $csrf)) {
                $error = 'Jeton de sécurité invalide. Merci de réessayer.';
            } elseif ($this->checkPassword($gallery, $submittedPassword)) {
                $this->unlock($request, $token);
                return $this->redirectToRoute('app_client_gallery_view', ['token' => $token]);
            } else {
                $error = 'Mot de passe incorrect.';
            }
        }

        return $this->render('client_gallery/login.html.twig', [
            'gallery' => $gallery,
            'error' => $error,
        ]);
    }

    #[Route('/galerie-client/{token}/photos', name: 'app_client_gallery_view', requirements: ['token' => '[a-f0-9]{32}'])]
    public function view(string $token, Request $request): Response
    {
        $check = $this->resolveAuth($token, $request);
        if ($check instanceof Response) {
            return $check;
        }

        $check->registerView();
        $this->em->flush();

        return $this->render('client_gallery/view.html.twig', [
            'gallery' => $check,
        ]);
    }

    #[Route('/galerie-client/{token}/photo/{photoId}', name: 'app_client_gallery_photo', requirements: ['token' => '[a-f0-9]{32}', 'photoId' => '\d+'])]
    public function photo(string $token, int $photoId, Request $request): Response
    {
        $check = $this->resolveAuth($token, $request);
        if ($check instanceof Response) {
            return $check;
        }

        return $this->serveFile($this->findPhotoInGallery($check, $photoId), 'inline');
    }

    #[Route('/galerie-client/{token}/download/{photoId}', name: 'app_client_gallery_download', requirements: ['token' => '[a-f0-9]{32}', 'photoId' => '\d+'])]
    public function download(string $token, int $photoId, Request $request): Response
    {
        $check = $this->resolveAuth($token, $request);
        if ($check instanceof Response) {
            return $check;
        }
        if (!$check->isAllowDownload()) {
            throw $this->createAccessDeniedException('Téléchargement désactivé pour cette galerie.');
        }

        return $this->serveFile($this->findPhotoInGallery($check, $photoId), 'attachment');
    }

    #[Route('/galerie-client/{token}/download-all', name: 'app_client_gallery_download_all', requirements: ['token' => '[a-f0-9]{32}'])]
    public function downloadAll(string $token, Request $request): Response
    {
        $check = $this->resolveAuth($token, $request);
        if ($check instanceof Response) {
            return $check;
        }
        if (!$check->isAllowDownload()) {
            throw $this->createAccessDeniedException('Téléchargement désactivé pour cette galerie.');
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'gallery_');
        $zip = new \ZipArchive();
        if ($zip->open($tmpFile, \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Impossible de créer l\'archive ZIP.');
        }

        foreach ($check->getPhotos() as $photo) {
            $filePath = $this->clientGalleriesDir.DIRECTORY_SEPARATOR.$photo->getImageName();
            if (is_file($filePath)) {
                $zip->addFile($filePath, $photo->getOriginalName() ?? $photo->getImageName());
            }
        }
        $zip->close();

        $filename = sprintf('%s-%s.zip', $this->slugify($check->getTitle()), date('Ymd'));

        $response = new StreamedResponse(function () use ($tmpFile) {
            readfile($tmpFile);
            @unlink($tmpFile);
        });
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename,
        ));
        $response->headers->set('Content-Length', (string) filesize($tmpFile));

        return $response;
    }

    #[Route('/galerie-client/{token}/logout', name: 'app_client_gallery_logout', requirements: ['token' => '[a-f0-9]{32}'])]
    public function logout(string $token, Request $request): Response
    {
        $request->getSession()->remove(self::SESSION_PREFIX.$token);
        $request->getSession()->remove(self::SESSION_PREFIX.$token.'_expires');
        return $this->redirectToRoute('app_client_gallery_login', ['token' => $token]);
    }

    // -- internals ---------------------------------------------------------

    /**
     * Returns the ClientGallery if authenticated, otherwise a Response
     * (404 if gallery doesn't exist / is expired, redirect to login if not unlocked).
     */
    private function resolveAuth(string $token, Request $request): ClientGallery|Response
    {
        $gallery = $this->repository->findOneByToken($token);
        if (!$gallery || !$gallery->isAccessible()) {
            return $this->render('client_gallery/unavailable.html.twig', [
                'gallery' => $gallery,
            ], new Response('', 404));
        }
        if (!$this->isUnlocked($request, $token)) {
            return new RedirectResponse($this->generateUrl('app_client_gallery_login', ['token' => $token]));
        }
        return $gallery;
    }

    private function isUnlocked(Request $request, string $token): bool
    {
        $session = $request->getSession();
        $unlocked = $session->get(self::SESSION_PREFIX.$token, false);
        $expires = (int) $session->get(self::SESSION_PREFIX.$token.'_expires', 0);

        if (!$unlocked || $expires < time()) {
            $session->remove(self::SESSION_PREFIX.$token);
            $session->remove(self::SESSION_PREFIX.$token.'_expires');
            return false;
        }
        return true;
    }

    private function unlock(Request $request, string $token): void
    {
        $session = $request->getSession();
        $session->set(self::SESSION_PREFIX.$token, true);
        $session->set(self::SESSION_PREFIX.$token.'_expires', time() + self::SESSION_LIFETIME);
    }

    private function checkPassword(ClientGallery $gallery, string $submitted): bool
    {
        if ($submitted === '' || !$gallery->getPasswordHash()) {
            return false;
        }
        $hasher = $this->hasherFactory->getPasswordHasher('common');
        return $hasher->verify($gallery->getPasswordHash(), $submitted);
    }

    private function findPhotoInGallery(ClientGallery $gallery, int $photoId): ClientPhoto
    {
        foreach ($gallery->getPhotos() as $p) {
            if ($p->getId() === $photoId) {
                return $p;
            }
        }
        throw new NotFoundHttpException('Photo introuvable dans cette galerie.');
    }

    private function serveFile(ClientPhoto $photo, string $disposition): BinaryFileResponse
    {
        $filePath = $this->clientGalleriesDir.DIRECTORY_SEPARATOR.$photo->getImageName();
        if (!is_file($filePath)) {
            throw new NotFoundHttpException('Fichier manquant sur le serveur.');
        }

        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(
            $disposition === 'attachment' ? ResponseHeaderBag::DISPOSITION_ATTACHMENT : ResponseHeaderBag::DISPOSITION_INLINE,
            $photo->getOriginalName() ?? $photo->getImageName(),
        );
        return $response;
    }

    private function slugify(?string $text): string
    {
        if (!$text) {
            return 'galerie';
        }
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
        $text = preg_replace('~[^a-zA-Z0-9]+~', '-', $text);
        return strtolower(trim($text, '-')) ?: 'galerie';
    }
}
