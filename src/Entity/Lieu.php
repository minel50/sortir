<?php

namespace App\Entity;

use App\Repository\LieuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=LieuRepository::class)
 * @UniqueEntity(fields={"nom"}, message="Ce nom de lieu est déjà utilisé")
 * @UniqueEntity(fields={"rue"}, message="Ce nom de rue est déjà utilisé")
 * @UniqueEntity(fields={"latitude", "longitude"}, message="Ces coordonnées sont déjà utilisées")
 */
class Lieu
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"liste_lieux"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, unique=true, name="nom")
     * @Assert\NotBlank(message="La saisie d'un nom de lieu est obligatoire")
     * @Assert\Length(min=2, max=50, minMessage="Veuillez saisir au moins 2 caractères", maxMessage="50 caractères maximum")
     * @Groups({"liste_lieux"})
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=50, name="rue")
     * @Assert\NotBlank(message="La saisie d'un nom de rue est obligatoire")
     * @Assert\Length(min=2, max=50, minMessage="Veuillez saisir au moins 2 caractères", maxMessage="50 caractères
     * maximum")
     */
    private $rue;

    /**
     * @Assert\NotBlank(message="La saisie de la latitute est obligatoire")
     * @Assert\Range(min=-90, max=90, notInRangeMessage="Vous devez renseigner une latitude entre -90° et +90°")
     * @ORM\Column(type="float", name="latitude")
     */
    private $latitude;

    /**
     * @Assert\NotBlank(message="La saisie de la longitude est obligatoire")
     * @Assert\Range(min=-180, max=180, notInRangeMessage="Vous devez renseigner une longitude entre -180° et +180°")
     * @ORM\Column(type="float", name="longitude")
     */
    private $longitude;

    /**
     * @ORM\ManyToOne(targetEntity=Ville::class, inversedBy="lieux")
     * @ORM\JoinColumn(nullable=false)
     */
    private $ville;

    /**
     * @ORM\OneToMany(targetEntity=Sortie::class, mappedBy="lieu")
     */
    private $sorties;

    public function __construct()
    {
        $this->sorties = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getRue(): ?string
    {
        return $this->rue;
    }

    public function setRue(string $rue): self
    {
        $this->rue = $rue;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getVille(): ?Ville
    {
        return $this->ville;
    }

    public function setVille(?Ville $ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    /**
     * @return Collection|Sortie[]
     */
    public function getSorties(): Collection
    {
        return $this->sorties;
    }

    public function addSorty(Sortie $sorty): self
    {
        if (!$this->sorties->contains($sorty)) {
            $this->sorties[] = $sorty;
            $sorty->setLieu($this);
        }

        return $this;
    }

    public function removeSorty(Sortie $sorty): self
    {
        if ($this->sorties->removeElement($sorty)) {
            // set the owning side to null (unless already changed)
            if ($sorty->getLieu() === $this) {
                $sorty->setLieu(null);
            }
        }

        return $this;
    }
}
