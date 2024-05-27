<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/logout', name: 'logout')]
class LogoutController extends AbstractController
{
    public function __invoke(): Response
    {
        $response = new Response();
        $response->headers->clearCookie('BEARER');

        return $response;
    }
}
