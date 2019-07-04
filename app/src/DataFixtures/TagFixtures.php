<?php
/**
 * Tag fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class TagFixtures.
 */
class TagFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    /**
     * Load.
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function loadData(ObjectManager $manager): void
    {
        $this->createMany(35, 'tags', function ($i) {
            $tag = new Tag();
            $tag->setName($this->faker->word);
            $tag->setCreatedAt($this->faker->dateTimeBetween('-100 days', '-1 days'));
            $tag->setUpdatedAt($this->faker->dateTimeBetween('-100 days', '-1 days'));

            for ($i = 0; $i <= 3; ++$i) {
                $tag->addArticle($this->getRandomReference('article'));
            }

            return $tag;
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
        return [ArticleFixtures::class];
    }
}
