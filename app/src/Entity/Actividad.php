<?php

namespace App\Entity;

use App\Repository\ActividadRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActividadRepository::class)]
class Actividad
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $nombre = null;

    #[ORM\Column(length: 50)]
    private ?string $tipo = null;

    #[ORM\Column(length: 30)]
    private ?string $estado = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $motivoCancelacion = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): static
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): static
    {
        $this->estado = $estado;

        return $this;
    }

    public function getMotivoCancelacion(): ?string
    {
        return $this->motivoCancelacion;
    }

    public function setMotivoCancelacion(?string $motivoCancelacion): static
    {
        $this->motivoCancelacion = $motivoCancelacion;

        return $this;
    }
}
