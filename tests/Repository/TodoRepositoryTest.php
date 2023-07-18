<?php

namespace App\Tests\Repository;

use App\Entity\Todo;
use App\Model\Paginator;
use App\Repository\TodoRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TodoRepositoryTest extends KernelTestCase
{
    private TodoRepository $repository;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $em = self::getContainer()->get("doctrine")->getManager();
        $this->repository = $em->getRepository(Todo::class);
    }

    /**
     * @throws Exception
     */
    public function testFindAllWithPagination(): void
    {
        $result = $this->repository->findAllWithPagination(1);

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertEquals(1, $result->getCurrentPage());
    }
}