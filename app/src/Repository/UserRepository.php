<?php
/**
 * User repository.
 */
namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class UserRepository.
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    /**
     * UserRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Finding author data method.
     *
     * @param User $user
     *
     * @return User[]
     */
    public function findAuthorData(User $user): array
    {
        $qb = $this->createQueryBuilder('u');
        if ($user->getAvatar()) {
            $qb->select('u.id', 'u.email', 'u.firstName', 'u.bio', 'u.blogName', 'u.roles', 'a.file AS avatar', 'a.id AS avatarId')
                ->innerJoin('App\Entity\Avatar', 'a', Join::WITH, 'a.user = u');
        } else {
            $qb->select('u.id', 'u.email', 'u.firstName', 'u.bio', 'u.blogName', 'u.roles');
        }
        $qb->andWhere('u.id = :userId')->setParameter('userId', $user->getId());

        return $qb->getQuery()->getResult();
    }

    /**
     * Finding followed author's articles method.
     *
     * @param int $userId
     *
     * @return QueryBuilder
     */
    public function findFollowedAuthorsArticles(int $userId): QueryBuilder
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u.id', 'u.articles', 'a.author', 'u.followedAuthors')
            ->innerJoin('App\Entity\Article', 'a', Join::WITH, 'u = u_f.followers')
            ->andWhere('u.id = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('a.publishedAt', 'DESC');

        return $qb;
    }

    /**
     * Search action.
     *
     * @param string $searchParam
     *
     * @return array
     */
    public function search(string $searchParam): array
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u')
            ->where('u.firstName like :searchParam')
            ->orWhere('u.blogName like :searchParam')
            ->setParameter('searchParam', '%'.$searchParam.'%')
            ->orderBy('u.firstName', 'ASC')
            ->getQuery()
            ->getResult();

        return $qb;
    }

    /**
     * Finding followed authors method.
     *
     * @param User $user
     *
     * @return QueryBuilder
     */
    public function findFollowedAuthors(User $user): QueryBuilder
    {
        $qb = $this->createQueryBuilder('u')
            ->innerJoin('App\Entity\User', 'f', Join::WITH, 'f = u')
            ->orderBy('f.firstName', 'ASC');

        foreach ($user->getFollowedAuthors() as $author) {
            $qb->orWhere('f.id = '.$author->getId());
        }

        return $qb;
    }

    /**
     * Finding all followers method.
     *
     * @param User $user
     *
     * @return QueryBuilder
     */
    public function findFollowers(User $user): QueryBuilder
    {
        $qb = $this->createQueryBuilder('u')
            ->innerJoin('App\Entity\User', 'f', Join::WITH, 'u = f')
            ->orderBy('f.firstName', 'ASC');

        foreach ($user->getFollowers() as $author) {
            $qb->orWhere('f.id = '.$author->getId());
        }

        return $qb;
    }

    /**
     * Query all records.
     *
     * @return QueryBuilder Query builder
     */
    public function queryAll(): QueryBuilder
    {
        return $this->getOrCreateQueryBuilder()
            ->orderBy('u.firstName', 'DESC');
    }

    /**
     * Save record.
     *
     * @param User $user User entity
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(User $user): void
    {
        $this->_em->persist($user);
        $this->_em->flush($user);
    }

    /**
     * Delete record.
     *
     * @param User $user User entity
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function delete(User $user): void
    {
        $this->_em->remove($user);
        $this->_em->flush($user);
    }

    /**
     * Get or create new query builder.
     *
     * @param QueryBuilder|null $queryBuilder Query builder
     *
     * @return QueryBuilder Query builder
     */
    private function getOrCreateQueryBuilder(QueryBuilder $queryBuilder = null): QueryBuilder
    {
        return $queryBuilder ?: $this->createQueryBuilder('u');
    }
}
