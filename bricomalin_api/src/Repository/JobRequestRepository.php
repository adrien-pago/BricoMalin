<?php

namespace App\Repository;

use App\Entity\JobRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JobRequest>
 */
class JobRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JobRequest::class);
    }

    public function save(JobRequest $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(JobRequest $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByFilters(?string $department = null, ?int $categoryId = null, ?string $q = null, ?string $status = null): array
    {
        $qb = $this->createQueryBuilder('jr')
            ->leftJoin('jr.category', 'c')
            ->addSelect('c');

        if ($department) {
            $qb->andWhere('jr.department = :department')
                ->setParameter('department', $department);
        }

        if ($categoryId) {
            $qb->andWhere('jr.category = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        if ($q) {
            $qb->andWhere('jr.title LIKE :q OR jr.description LIKE :q')
                ->setParameter('q', '%' . $q . '%');
        }

        if ($status) {
            $qb->andWhere('jr.status = :status')
                ->setParameter('status', $status);
        }

        $qb->orderBy('jr.createdAt', 'DESC');

        return $qb->getQuery()->getResult();
    }
}

