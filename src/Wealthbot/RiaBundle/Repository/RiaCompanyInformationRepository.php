<?php

namespace Wealthbot\RiaBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * RiaCompanyInformationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class RiaCompanyInformationRepository extends EntityRepository
{

    public function getByUserIdJoinedWithPortfolioModel($userId)
    {
        return $this->createQueryBuilder('rci')
            ->leftJoin('rci.portfolioModel', 'pm')
            ->where('rci.ria_user_id = :ria_user_id')
            ->setParameter('ria_user_id', $userId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findBySlugAndNotRiaUserId($slug, $riaUserId)
    {
        $qb = $this->createQueryBuilder('rci');
        $qb->where('rci.slug = :slug')
            ->andWhere('rci.ria_user_id != :ria_user_id')
            ->setParameter('slug', $slug)
            ->setParameter('ria_user_id', $riaUserId);

        return $qb->getQuery()->getOneOrNullResult();
    }

    

}
