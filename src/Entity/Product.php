<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\NonUniqueResultException;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 */
class Product
{
    use SoftDeleteableEntity;

    public static $product_statuses = [
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
    private $issn;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

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
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer", inversedBy="products")
     * @ORM\JoinColumn(nullable=true)
     */
    private $customer;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getIssn(): int
    {
        return $this->issn;
    }

    /**
     * @param mixed $issn
     * @return Product
     */
    public function setIssn(string $issn): self
    {
        $this->issn = $issn;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return Product
     */
    public function setName(string $name): self
    {
        $this->name = $name;

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
     * @return Product
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;

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
     * @return Product
     */
    public function setCreatedAt(DateTime $createdAt): self
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
     * @return Product
     */
    public function setUpdatedAt(DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Customer
     */
    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    /**
     * @param Customer
     * @return Product
     */
    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @param EntityManagerInterface $em
     * @return array
     */
    public static function notDeletedProducts(EntityManagerInterface $em): array
    {
        return $em->createQueryBuilder()
            ->select('p')
            ->from('\App\Entity\Product', 'p')
            ->where("p.deletedAt IS NULL")
            ->getQuery()
            ->getResult();
    }

    /**
     * @param EntityManagerInterface $em
     * @param int $id
     * @return Product|null
     */
    public static function notDeletedProductById(EntityManagerInterface $em, int $id): ?Product
    {
        try {
            $data = $em->createQueryBuilder()
                ->select('p')
                ->from('\App\Entity\Product', 'p')
                ->where("p.id = $id")
                ->andWhere("p.deletedAt IS NULL")
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            $data = null;
        }

        return $data;
    }

    /**
     * @param EntityManagerInterface $em
     * @param $data_ago
     * @return mixed
     */
    public static function getPendingProducts(EntityManagerInterface $em, $data_ago): ?array
    {
        // List of products created less than $data_ago weeks ago
        return $em->createQueryBuilder()
            ->select('p')
            ->from('\App\Entity\Product', 'p')
            ->where("p.status = 'pending'")
            ->andWhere("p.createdAt < '$data_ago'")
            ->andWhere("p.deletedAt IS NULL")
            ->orderBy('p.createdAt', 'asc')
            ->getQuery()
            ->getResult();
    }

}
