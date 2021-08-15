<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class AppCustomAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;
    public const LOGIN_ROUTE = "app_login";

    private $entityManager;
    private $urlGenerator;
    private $csrfTokenManager;
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * should this authenticator be used for this request
     * @param Request $request
     * @return bool|void
     */
    public function supports(Request $request)
    {
        // TODO: Implement supports() method.

        return self::LOGIN_ROUTE === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    /* Gather the credentials which need to be checked in order to authenticate.
    *
    * @param Request $request
    * @return array
    */
    public function getCredentials(Request $request) #First
    {
        // TODO: Implement getCredentials() method.
        $credentials = [
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),

        ];
        $request->getSession()->set(Security::LAST_USERNAME, $credentials['email']);
        return $credentials;
    }

    /**
     * Return a UserInterface object based on the credentials.
     *
     * @param array $credentials
     * @param UserProviderInterface $userProvider
     * @return object|UserInterface|null
     */

    public function getUser($credentials, UserProviderInterface $userProvider) #Second
    {
        // TODO: Implement getUser() method.
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        $user  = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $credentials['email']]) ?? throw  new CustomUserMessageAuthenticationException('Email could not be found.');
        return  $user;
    }

    /**
     * Check credentials
     *
     * Check csrf token is valid
     * Check password is valid
     *
     * @param array $credentials
     * @param UserInterface $user
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user) #Third
    {
        // TODO: Implement checkCredentials() method.


        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    /**
     * What should happen once the user is authenticated?
     *
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     * @return Response|void|null
     */


    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        // 1. Try to redirect the user to their original intended path
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {

            return new RedirectResponse($targetPath);
        }

        // 2. If not, redirect to homepage
        return new RedirectResponse($this->urlGenerator->generate('home'));
    }


    /**
     * On failure
     *
     * @return string
     */

    protected function getLoginUrl()
    {
        // TODO: Implement getLoginUrl() method.
        return $this->urlGenerator->generate('app_login');
    }
}




//https://github.com/symfony/password-hasher