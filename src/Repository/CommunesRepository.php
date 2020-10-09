<?php

namespace App\Repository;

use App\Entity\Communes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Communes|null find($id, $lockMode = null, $lockVersion = null)
 * @method Communes|null findOneBy(array $criteria, array $orderBy = null)
 * @method Communes[]    findAll()
 * @method Communes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommunesRepository extends ServiceEntityRepository
{
    /**
     * CommunesRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Communes::class);
    }

    /**
     * @return int|mixed|string
     */
    public function findAllCommunes()
    {
        $query = $this->createQueryBuilder('a');
        return $query->getQuery()->getResult(Query::HYDRATE_ARRAY);
    }
}
