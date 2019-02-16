<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PortalRepository")
 */
class Portal
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $domain;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $access_token;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $refresh_token;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $member_id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $expires;

    /**
     * @ORM\Column(type="datetime")
     */
    private $changed;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    public function getAccessToken(): ?string
    {
        return $this->access_token;
    }

    public function setAccessToken(string $access_token): self
    {
        $this->access_token = $access_token;

        return $this;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refresh_token;
    }

    public function setRefreshToken(string $refresh_token): self
    {
        $this->refresh_token = $refresh_token;

        return $this;
    }

    public function getMemberId(): ?string
    {
        return $this->member_id;
    }

    public function setMemberId(string $member_id): self
    {
        $this->member_id = $member_id;

        return $this;
    }

    public function getExpires(): ?\DateTimeInterface
    {
        return $this->expires;
    }

    public function setExpires(?\DateTimeInterface $expires): self
    {
        $this->expires = $expires;

        return $this;
    }

    public function getChanged(): ?\DateTimeInterface
    {
        return $this->changed;
    }

    public function setChanged(\DateTimeInterface $changed): self
    {
        $this->changed = $changed;

        return $this;
    }
}
