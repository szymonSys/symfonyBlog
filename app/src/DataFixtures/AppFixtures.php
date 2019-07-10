<?php
/**
 * App fixtures.
 */
namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class AppFixtures.
 */
class AppFixtures extends Fixture
{
    /**
     * Load action.
     *
     * @param ObjectManager $manager Object manager
     */
    public function load(ObjectManager $manager)
    {
        $manager->flush();
    }
}
