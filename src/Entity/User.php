<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $username = '';

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password = '';

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email = '';

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $token = '';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): User
    {
        $this->token = $token;

        return $this;
    }

    public function eraseCredentials():void
    {
    }

    public function getRoles():array
    {
        return array('ROLE_USER');
    }

    public function getSalt():void
    {
    }
}
