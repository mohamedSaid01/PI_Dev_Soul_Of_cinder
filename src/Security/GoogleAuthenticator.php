<?php

namespace App\Security;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use App\Repository\UserRepository;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Enum\Gender; // Assurez-vous d'importer l'enum Gender

class GoogleAuthenticator extends OAuth2Authenticator implements AuthenticationEntryPointInterface
{
    private $clientRegistry;
    private $router;
    private $userRepository;
    private $entityManager;

    public function __construct(
        ClientRegistry $clientRegistry,
        RouterInterface $router,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->clientRegistry = $clientRegistry;
        $this->router = $router;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('google');
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client) {
                $googleUser = $client->fetchUserFromToken($accessToken);

                // Récupérer l'e-mail de l'utilisateur Google
                $email = $googleUser->getEmail();

                // Rechercher l'utilisateur dans la base de données
                $existingUser = $this->userRepository->findOneBy(['email' => $email]);

                if ($existingUser) {
                    // Si l'utilisateur existe, retournez-le
                    return $existingUser;
                }

                // Si l'utilisateur n'existe pas, créez-en un nouveau
                $user = new User();
                $user->setEmail($email);
                $user->setFirstName($googleUser->getFirstName()); // Si disponible
                $user->setLastName($googleUser->getLastName());   // Si disponible
                $user->setPassword(''); // Vous pouvez générer un mot de passe aléatoire ou laisser vide

                // Définir une valeur par défaut pour le genre
                $user->setGender(Gender::OTHER); // Utilisez une valeur par défaut de votre enum Gender

                // Enregistrez le nouvel utilisateur dans la base de données
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Redirigez l'utilisateur après une connexion réussie.
        return new RedirectResponse($this->router->generate('display_front'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new RedirectResponse($this->router->generate('app_login'));
    }
}