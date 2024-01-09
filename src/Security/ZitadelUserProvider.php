<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Drenso\OidcBundle\Exception\OidcException;
use Drenso\OidcBundle\Model\OidcUserData;
use Drenso\OidcBundle\Security\UserProvider\OidcUserProviderInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ZitadelUserProvider implements UserProviderInterface, OidcUserProviderInterface, LoggerAwareInterface
{
    protected EntityManagerInterface $em;
    protected UserRepository $repo;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repo = $entityManager->getRepository(User::class);
        $this->em = $entityManager;
    }

    private LoggerInterface $logger;

    /**
     * @see LoggerAwareInterface
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Symfony calls this method if you use features like switch_user
     * or remember_me. If you're not using these features, you do not
     * need to implement this method.
     *
     * @throws UserNotFoundException if the user is not found
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        // Load a User object from your data source or throw UserNotFoundException.
        // The $identifier argument is whatever value is being returned by the
        // getUserIdentifier() method in your User class.
        $user = $this->repo->findOneBySub($identifier);
        if (!$user) {
            throw new UserNotFoundException(sprintf('User with id "%s" not found'));
        }
        return $user;
    }

    /**
     * Refreshes the user after being reloaded from the session.
     *
     * When a user is logged in, at the beginning of each request, the
     * User object is loaded from the session and then this method is
     * called. Your job is to make sure the user's data is still fresh by,
     * for example, re-querying for fresh User data.
     *
     * If your firewall is "stateless: true" (for a pure API), this
     * method is not called.
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }

        // Return a User object after making sure its data is "fresh".
        // Or throw a UserNotFoundException if the user no longer exists.
        $refreshedUser = $this->repo->find($user->getId());
        if (!$user) {
            throw new UserNotFoundException(sprintf('User with id "%s" not found', $user->getId()));
        }
        return $refreshedUser;
    }

    /**
     * Tells Symfony to use this provider for this User class.
     */
    public function supportsClass(string $class): bool
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }

    /**
     * Parse lower case plain role names from zitadel to the
     * Symfony `ROLE_USER` format.
     */
    private function parseZitadelRoles(array $roles): array
    {
        $symfonyRoles = [];
        foreach ($roles as $role => $data) {
            $role = strtoupper($role);
            if (!str_starts_with($role, 'ROLE_')) {
                $role = 'ROLE_' . $role;
            }
            array_push($symfonyRoles, $role);
        }
        return $symfonyRoles;
    }

    /**
     * Zitadel reserved roles claim.
     * See https://zitadel.com/docs/apis/openidoauth/claims#reserved-claims for other available claims.
     */
    const ROLES_CLAIM = 'urn:zitadel:iam:org:project:roles';
    
    /**
     * Requested scopes. Adjust to your application's needs.
     * See https://zitadel.com/docs/apis/openidoauth/scopes for all available scopes. 
     */
    const SCOPES = array('openid', 'profile', 'email', self::ROLES_CLAIM);

    /**
     * Copy Zitadel User Info to the Symfony User Entity.
     * The available info depends on the scopes defined in the SCOPES constant above.
     */
    private function updateUserEntity(User &$user, OidcUserData $userData)
    {
        $user->setSub($userData->getSub());
        $user->setRoles($this->parseZitadelRoles($userData->getUserDataArray(self::ROLES_CLAIM)));
        $user->setDisplayName($userData->getDisplayName());
        $user->setFullName($userData->getFullName());
        $user->setEmail($userData->getEmail());
        $user->setEmailVerified($userData->getEmailVerified());
        $user->setUpdatedAt(new DateTimeImmutable());
    }

    /**
     * Create or update an user with User Info from Zitadel.
     * 
     * @see OidcUserProviderInterface
     */
    public function ensureUserExists(string $userIdentifier, OidcUserData $userData)
    {
        $this->logger->debug("OIDC User Data", [
            'sub' => $userData->getSub(),
            'display_name' => $userData->getDisplayName(),
            'full_name' => $userData->getFullName(),
            'roles' => $userData->getUserData(self::ROLES_CLAIM),
        ]);

        try {
            $user = $this->repo->findOneBySub($userIdentifier);
            if (!$user) {
                $user = new User();
                $user->setCreatedAt(new DateTimeImmutable());
                $this->em->persist($user);
            }
            $this->updateUserEntity($user, $userData);
            $this->em->flush();
        } catch (\Throwable $th) {
            throw new OidcException('cannot create user', previous: $th);
        }
    }

    /**
     * @see OidcUserProviderInterface
     */
    public function loadOidcUser(string $userIdentifier): UserInterface
    {
        return $this->loadUserByIdentifier($userIdentifier);
    }
}
