<?php
/**
 * Article repository.
 */
namespace App\Repository;

use App\Entity\Article;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class ArticleRepository.
 *
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    /**
     * ArticleRepository constructor.
     *
     * @param RegistryInterface $registry Registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * Finding all articles than category.
     *
     * @param string $categoryName Category name
     *
     * @return QueryBuilder Query builder
     */
    public function findAllThanCategory($categoryName): QueryBuilder
    {
        $qb = $this->createQueryBuilder('a')
            ->innerJoin('a.category', 'c')
            ->andWhere('c.name = :category')
            ->setParameter('category', $categoryName)
            ->orderBy('a.publishedAt', 'DESC');

        return $qb;
    }

    /**
     * Finding all articles than user id.
     *
     * @param int $userId User Id
     *
     * @return QueryBuilder Query builder
     */
    public function findAllThanUserId(int $userId): QueryBuilder
    {
        $qb = $this->createQueryBuilder('a')
            ->innerJoin('App\Entity\User', 'u', Join::WITH, 'u = a.author')
            ->andWhere('u.id = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('a.publishedAt', 'DESC');
//            ->getQuery();
        return $qb;
    }

    /**
     * Find followed authors's articles.
     *
     * @param User $user
     *
     * @return QueryBuilder
     */
    public function findAllByFollowed(User $user): QueryBuilder
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a')
            ->join('App\Entity\User', 'f', Join::WITH, 'f = a.author')
            ->orderBy('a.publishedAt', 'DESC');
        foreach ($user->getFollowedAuthors() as $author) {
            $qb->orWhere('f.id = '.$author->getId());
        }

        return $qb;
    }

//    public function findByTag(Tag $tag): QueryBuilder
//    {
//        $qb = $this->createQueryBuilder('a')
//            ->select('a')
//            ->innerJoin('App\Entity\Tag', 't', Join::WITH, 'a.tags = t')
//            ->andWhere()
//
//    }

    /**
     * Search action.
     *
     * @param string $searchParam
     *
     * @return array
     */
    public function search(string $searchParam): array
    {
        $qb = $this->createQueryBuilder('a')
        ->select('a')
        ->where('a.title like :searchParam')
        ->setParameter('searchParam', '%'.$searchParam.'%')
        ->orderBy('a.publishedAt', 'DESC')
        ->getQuery()
        ->getResult();

        return $qb;
    }

    /**
     * Find by tag action.
     *
     * @param Tag $tag
     *
     * @return QueryBuilder
     */
    public function findByTag(Tag $tag): QueryBuilder
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a')
            ->join('App\Entity\Tag', 't');
        foreach ($tag->getArticles() as $article) {
            $qb->orWhere('a.id = '.$article->getId());
        }
//            $qb->orWhere('t.articles = :articles')
//            ->setParameter('articles', $tag->getArticles())
        $qb->orderBy('a.publishedAt', 'DESC');

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
            ->orderBy('a.publishedAt', 'DESC');
    }

    /**
     * Save record.
     *
     * @param Article $article Article entity
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(Article $article): void
    {
        $this->_em->persist($article);
        $this->_em->flush($article);
    }

    /**
     * Delete record.
     *
     * @param Article $article Article entity
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function delete(Article $article): void
    {
        $this->_em->remove($article);
        $this->_em->flush($article);
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
        return $queryBuilder ?: $this->createQueryBuilder('a');
    }

    // /**
    //  * @return Article[] Returns an array of Article objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Article
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
