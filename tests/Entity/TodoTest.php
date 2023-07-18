<?php

namespace App\Tests\Entity;

use App\Entity\Todo;
use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TodoTest extends KernelTestCase
{
    private EntityManager $entityManager;
    private ValidatorInterface  $validator;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->entityManager = self::getContainer()->get('doctrine')->getManager();
        $this->validator = self::getContainer()->get("validator");
    }

    public function testDefaultValues(): void
    {
        $todo = new Todo();

        // Test default values
        $this->assertNull($todo->getId());
        $this->assertNull($todo->getTitle());
        $this->assertNull($todo->getCreatedAt());
        $this->assertNull($todo->getUpdatedAt());
        $this->assertFalse($todo->isCompleted());
    }

    public function testTitle()
    {
        $todo = new Todo();

        // Test entity constraints
        /** @var ConstraintViolation[] $errors */
        $errors = $this->validator->validateProperty($todo, "title");
        $this->assertInstanceOf(NotBlank::class, $errors[0]->getConstraint());

        $todo->setTitle("Legends and Lore of a Multifaceted World: A Magnum Opus Spanning Millennia, Unearthing the Myths, Histories, and Cultural Marvels of Diverse Civilizations, from Ancient Wonders to Modern Marvels, Celebrating the Endless Tapestry of Humanity's Past, Present, and Imagined Futures in the Labyrinthine Pathways of Time and Space, Embarking on a Whimsical Odyssey of Knowledge, Wisdom, and Creativity for Generations Yet to Come");
        /** @var ConstraintViolation[] $errors */
        $errors = $this->validator->validateProperty($todo, "title");
        $this->assertInstanceOf(Length::class, $errors[0]->getConstraint());

        // Test the title setter and getter methods
        $title = 'Test Todo';
        $todo->setTitle($title);
        $this->assertEquals($title, $todo->getTitle());
    }

    public function testCompleted()
    {
        $todo = new Todo();

        // Test the completed setter and getter methods
        $todo->setCompleted(true);
        $this->assertTrue($todo->isCompleted());
    }

    /**
     * @throws ORMException
     */
    public function testDoctrineEvents()
    {
        $todo = new Todo();

        // Persist the entity (not flush) in order to generate the createdAt and updatedAt fields
        $this->entityManager->persist($todo);

        // Test the createdAt and updatedAt setter and getter methods
        $this->assertInstanceOf(DateTimeImmutable::class, $todo->getCreatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $todo->getUpdatedAt());

        // Detach the entity to prevent tracking unused entity
        $this->entityManager->detach($todo);
    }
}
