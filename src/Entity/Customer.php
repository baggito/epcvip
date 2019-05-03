<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;


/**
 * @ORM\Entity(repositoryClass="App\Repository\CustomerRepository")
 */
class Customer
{
    use SoftDeleteableEntity;

    public static $customer_statuses = [
        'new', 'pending', 'in review', 'approved', 'inactive', 'deleted'
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, unique=true)
     */
    private $uuid;

    /**
     * @ORM\Column(type="string", length=30, name="first_name")
     */
    private $firstName;

    /**
     * @ORM\Column (type="string", length=30, name="last_name")
     */
    private $lastName;

    /**
     * @ORM\Column(type="datetime", name="date_of_birth")
     */
    private $dateOfBirth;

    /**
     * Seems options={"default"="new"} dose not work for ENUM type, I added default value in migration SQL code manually.
     *
     * @ORM\Column(type="string", columnDefinition="ENUM('new', 'pending', 'in review', 'approved', 'inactive', 'deleted')")
     */
    private $status;

    /**
     * @ORM\Column(type="datetime", name="created_at")
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true, name="updated_at")
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Product", mappedBy="customer")
     */
    private $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @param mixed $uuid
     * @return Customer
     */
    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     * @return Customer
     */
    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     * @return Customer
     */
    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDateOfBirth(): DateTime
    {
        return $this->dateOfBirth;
    }

    /**
     * @param mixed $dateOfBirth
     * @return Customer
     */
    public function setDateOfBirth(DateTime $dateOfBirth): self
    {
        if ($dateOfBirth instanceof DateTime) {
            $this->dateOfBirth = $dateOfBirth;
        } else {
            try {
                $date = new DateTime($dateOfBirth);
            } catch (Exception $e) {
                $date = date('Y-m-d H:i:s');
            }
            $this->dateOfBirth = $date;
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     * @return Customer
     */
    public function setStatus(?string $status): self
    {
        if (! $status) {
            $this->status = 'new';
        } else {
            $this->status = $status;
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     * @return Customer
     */
    public function setCreatedAt(DateTime  $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param mixed $updatedAt
     * @return Customer
     */
    public function setUpdatedAt(DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection|Product[]
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    /**
     * Add product to customer
     *
     * @param Product $product
     * @return Customer
     */
    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
            $product->setCustomer($this);
        }

        return $this;
    }

    /**
     * Remove product from customer
     *
     * @param Product $product
     * @return Customer
     */
    public function removeProduct(Product $product): self
    {
        if ($this->products->contains($product)) {
            $this->products->removeElement($product);
            // set the owning side to null (unless already changed)
            if ($product->getCustomer() === $this) {
                $product->setCustomer(null);
            }
        }

        return $this;
    }

    /**
     * Returns the list of all not deleted customers
     *
     * @param EntityManagerInterface $em
     * @return array
     */
    public static function notDeletedCustomers(EntityManagerInterface $em): array
    {
        return $em->createQueryBuilder()
            ->select('c')
            ->from('\App\Entity\Customer', 'c')
            ->where("c.deletedAt IS NULL")
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns not deleted customer by id
     *
     * @param EntityManagerInterface $em
     * @param int $id
     * @return Customer|null
     */
    public static function notDeletedCustomerById(EntityManagerInterface $em, int $id): ?Customer
    {
        try {
            $data = $em->createQueryBuilder()
                ->select('c')
                ->from('\App\Entity\Customer', 'c')
                ->where("c.id = $id")
                ->andWhere("c.deletedAt IS NULL")
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            $data = null;
        }

        return $data;
    }
}
