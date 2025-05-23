<?php

/*
 * This file is part of the enhavo package.
 *
 * (c) WE ARE INDEED GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Enhavo\Bundle\UserBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Enhavo\Bundle\AppBundle\Model\TimestampableTrait;

class User implements UserInterface, ApiTokenAwareInterface
{
    use TimestampableTrait;

    private ?int $id = null;
    private string $userIdentifier;
    private ?string $firstName = null;
    private ?string $lastName = null;
    private bool $enabled = true;
    private bool $verified = false;
    private ?string $username = null;
    private ?string $email = null;
    private ?string $password = null;
    private ?string $plainPassword = null;
    private ?\DateTime $lastLogin = null;
    private ?string $confirmationToken = null;
    private ?\DateTime $passwordRequestedAt = null;
    private ?\DateTime $lastFailedLoginAttempt = null;
    private bool $apiAccess = false;
    private ?string $apiToken = null;
    private ?\DateTime $apiTokenCreatedAt = null;

    /** @var GroupInterface[] */
    private $groups;

    /** @var string[] */
    private array $roles = [];

    private ?int $failedLoginAttempts = null;
    private ?\DateTime $passwordUpdatedAt = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }

    public function setUserIdentifier(string $userIdentifier): void
    {
        $this->userIdentifier = $userIdentifier;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(static::ROLE_ADMIN);
    }

    public function setAdmin($boolean)
    {
        if (true === $boolean) {
            $this->addRole(static::ROLE_ADMIN);
        } else {
            $this->removeRole(static::ROLE_ADMIN);
        }
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(static::ROLE_SUPER_ADMIN);
    }

    public function setSuperAdmin(bool $superAdmin)
    {
        if ($superAdmin) {
            $this->addRole(static::ROLE_SUPER_ADMIN);
        } else {
            $this->removeRole(static::ROLE_SUPER_ADMIN);
        }
    }

    public function getGroupNames(): array
    {
        $names = [];
        foreach ($this->groups->getValues() as $group) {
            $names[] = $group->getName();
        }

        return $names;
    }

    public function addGroup(GroupInterface $group)
    {
        if (!$this->getGroups()->contains($group)) {
            $this->getGroups()->add($group);
        }
    }

    public function removeGroup(GroupInterface $group)
    {
        if ($this->getGroups()->contains($group)) {
            $this->getGroups()->removeElement($group);
        }
    }

    public function addRole($role)
    {
        $role = strtoupper($role);

        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;

        foreach ($this->getGroups()->getValues() as $group) {
            $roles = array_merge($roles, $group->getRoles());
        }

        // we need to make sure to have at least one role
        $roles[] = static::ROLE_DEFAULT;

        return array_unique($roles);
    }

    public function hasRole($role): bool
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    public function isEnabled(): bool
    {
        return true === $this->enabled;
    }

    public function removeRole($role)
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }
    }

    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;
    }

    public function setLastLogin(?\DateTime $time = null)
    {
        $this->lastLogin = $time;
    }

    public function setConfirmationToken($confirmationToken)
    {
        $this->confirmationToken = $confirmationToken;
    }

    public function setPasswordRequestedAt(?\DateTime $date = null)
    {
        $this->passwordRequestedAt = $date;
    }

    public function getPasswordRequestedAt(): ?\DateTime
    {
        return $this->passwordRequestedAt;
    }

    public function isPasswordRequestNonExpired($ttl): bool
    {
        return $this->getPasswordRequestedAt() instanceof \DateTime
               && $this->getPasswordRequestedAt()->getTimestamp() + $ttl > time();
    }

    public function setRoles(array $roles)
    {
        $this->roles = [];

        foreach ($roles as $role) {
            $this->addRole($role);
        }
    }

    public function getGroups()
    {
        return $this->groups;
    }

    public function hasGroup($name)
    {
        return in_array($name, $this->getGroupNames());
    }

    public function __toString()
    {
        return (string) ($this->getEmail() ?? $this->getUsername());
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): void
    {
        $this->username = $username;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function getLastLogin(): ?\DateTime
    {
        return $this->lastLogin;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function isVerified(): bool
    {
        return $this->verified;
    }

    public function setVerified(bool $verified): void
    {
        $this->verified = $verified;
    }

    public function getFailedLoginAttempts(): int
    {
        return $this->failedLoginAttempts ?? 0;
    }

    public function setFailedLoginAttempts(int $failedLoginAttempts): void
    {
        $this->failedLoginAttempts = $failedLoginAttempts;
    }

    public function getPasswordUpdatedAt(): ?\DateTime
    {
        return $this->passwordUpdatedAt;
    }

    public function setPasswordUpdatedAt(?\DateTime $passwordUpdatedAt): void
    {
        $this->passwordUpdatedAt = $passwordUpdatedAt;
    }

    public function getLastFailedLoginAttempt(): ?\DateTime
    {
        return $this->lastFailedLoginAttempt;
    }

    public function setLastFailedLoginAttempt(?\DateTime $lastFailedLoginAttempt): void
    {
        $this->lastFailedLoginAttempt = $lastFailedLoginAttempt;
    }

    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    public function setApiToken(?string $token): void
    {
        $this->apiToken = $token;
    }

    public function getApiAccess(): bool
    {
        return $this->apiAccess;
    }

    public function setApiAccess(bool $apiAccess): void
    {
        $this->apiAccess = $apiAccess;
    }

    public function getApiTokenCreatedAt(): ?\DateTime
    {
        return $this->apiTokenCreatedAt;
    }

    public function setApiTokenCreatedAt(?\DateTime $apiTokenCreatedAt): void
    {
        $this->apiTokenCreatedAt = $apiTokenCreatedAt;
    }
}
