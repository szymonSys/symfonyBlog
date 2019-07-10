<?php
/**
 * Article fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Comment;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class CommentFixtures.
 */
class CommentFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    /**
     * Load.
     *
     * @param ObjectManager $manager
     */
    public function loadData(ObjectManager $manager): void
    {
        $this->createMany(60, 'comment', function () {
            $comment = new Comment();
            $comment->setCreatedAt($this->faker->dateTimeBetween('-100 days', '-1 days'));
            $comment->setUpdatedAt($this->faker->dateTimeBetween('-100 days', '-1 days'));
            $comment->setContent($this->faker->paragraph);
            $comment->setArticle($this->getRandomReference('article'));
            $comment->setAuthor($this->getRandomReference('users'));

            return $comment;
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
        return [ArticleFixtures::class, UserFixtures::class];
    }
}
