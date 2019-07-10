<?php
/**
 * Article fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Article;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class ArticleFixtures.
 */
class ArticleFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    /**
     * Load.
     *
     * @param ObjectManager $manager
     */
    public function loadData(ObjectManager $manager): void
    {
        $this->createMany(50, 'article', function () {
            $article = new Article();
            $article->setTitle($this->faker->sentence);
            $article->setPublishedAt($this->faker->dateTimeBetween('-100 days', '-1 days'));
            $article->setBody($this->faker->paragraph);
            $article->setCategory($this->getRandomReference('categories'));
            $article->setAuthor($this->getRandomReference('users'));

            return $article;
        });

        $manager->flush();
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on.
     *
     * @return array Array of dependencies
     */
    public function getDependencies(): array
    {
        return [CategoryFixtures::class, UserFixtures::class];
    }
}
