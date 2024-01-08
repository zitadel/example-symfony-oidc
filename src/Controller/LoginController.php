<?php

namespace App\Controller;

use App\Security\ZitadelUserProvider;
use Drenso\OidcBundle\OidcClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class LoginController extends AbstractController
{
    /**
     * This controller forwards the user to the OIDC login
     *
     * @throws \Drenso\OidcBundle\Exception\OidcException
     */
    #[Route('/login', name: 'login')]
    #[IsGranted('PUBLIC_ACCESS')]
    public function surfconext(OidcClientInterface $oidcClient): RedirectResponse
    {
        // Redirect to authorization @ OIDC provider
        return $oidcClient->generateAuthorizationRedirect(scopes: ZitadelUserProvider::SCOPES);
    }
}
