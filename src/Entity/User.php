<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @Assert\Type(
     *     type="string",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     */
    private $username = '';

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password = '';

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email."
     * )
     */
    private $email = '';

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Type(
     *     type="string",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     */
    private $token = '';

    /**
     * @var Collection|Role[]
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Role")
     * @ORM\JoinTable(
     *     name="user_role",
     *     joinColumns={@ORM\JoinColumn(name="user", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="role", referencedColumnName="id")}
     * )
     */
    private $roles;

    public const PASSWORD_LENGTH = 8;

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

    public function eraseCredentials(): void
    {
    }

    public function getRoles(): array
    {
        $roles = [];
        foreach ($this->roles as $role) {
            $roles[] = $role->getName();
        }

        return $roles;
    }

    public function setRoles(array $roles): self
    {
        $collection = new ArrayCollection();
        foreach ($roles as $role) {
            if (!$collection->contains($role)) {
                $collection->add($role);
            }
        }

        $this->roles = $collection;

        return $this;
    }

    public function getSalt(): void
    {
    }
}
