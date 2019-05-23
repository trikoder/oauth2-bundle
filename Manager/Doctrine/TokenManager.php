<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Manager\Doctrine;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

abstract class TokenManager
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var string
     */
    protected $class;

    /**
     * TokenManager constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function deleteExpired()
    {
        $now = new \DateTime();

        /** @var QueryBuilder $qb */
        $qb = $this
            ->entityManager
            ->createQueryBuilder();

        $result = $qb->delete($this->getClass(), 'e')
            ->where($qb->expr()->lte('e.expiry', ':date'))
            ->setParameter(':date', $now)
            ->getQuery()
            ->getResult();

        return $result;
    }
}