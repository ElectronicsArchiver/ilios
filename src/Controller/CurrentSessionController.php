<?php

declare(strict_types=1);

namespace App\Controller;

use App\Classes\SessionUserInterface;
use App\Classes\CurrentSession;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class CurrentSessionController
 * Current session reflects back the user from the token
 */
class CurrentSessionController extends AbstractController
{
    /**
     * Gets the currently authenticated users Id
     *
     * @param string $version
     */
    public function getAction($version, TokenStorageInterface $tokenStorage, SerializerInterface $serializer): Response
    {
        $sessionUser = $tokenStorage->getToken()->getUser();
        if (!$sessionUser instanceof SessionUserInterface) {
            throw new NotFoundHttpException('No current session');
        }
        $currentSession = new CurrentSession($sessionUser);

        return new Response(
            $serializer->serialize($currentSession, 'json'),
            Response::HTTP_OK,
            ['Content-type' => 'application/json']
        );
    }
}
