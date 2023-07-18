<?php

namespace App\Controller;

use App\Entity\Todo;
use App\OptionsResolver\PaginatorOptionsResolver;
use App\OptionsResolver\TodoOptionsResolver;
use App\Repository\TodoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route("/api", "api_", format: "json")]
class TodoController extends AbstractController
{
    /**
     * @throws Exception
     */
    #[Route('/todos', name: 'get_todos', methods: ["GET"])]
    public function getTodos(TodoRepository $todoRepository, Request $request, PaginatorOptionsResolver $paginatorOptionsResolver): JsonResponse
    {
        try {
            $queryParams = $paginatorOptionsResolver
                ->configurePage()
                ->resolve($request->query->all());

            $todos = $todoRepository->findAllWithPagination($queryParams["page"]);

            return $this->json($todos);

        } catch (Exception $exception) {
            throw new BadRequestHttpException($exception->getMessage());
        }
    }

    #[Route("/todos/{id}", "get_todo", methods: ["GET"])]
    public function getTodo(Todo $todo): JsonResponse
    {
        return $this->json($todo);
    }

    #[IsGranted("IS_AUTHENTICATED")]
    #[Route("/todos", "create_todo", methods: ["POST"])]
    public function createTodo(Request $request, TodoRepository $todoRepository, ValidatorInterface $validator, TodoOptionsResolver $todoOptionsResolver): JsonResponse
    {
        try {
            $requestBody = json_decode($request->getContent(), true);

            $fields = $todoOptionsResolver->configureTitle(true)->resolve($requestBody);

            $todo = new Todo();
            $todo->setTitle($fields["title"]);

            $errors = $validator->validate($todo);
            if (count($errors) > 0) {
                throw new InvalidArgumentException((string) $errors);
            }

            $todoRepository->save($todo, true);

            return $this->json($todo, status: Response::HTTP_CREATED);

        } catch (Exception $exception) {
            throw new BadRequestHttpException($exception->getMessage());
        }
    }

    #[IsGranted("IS_AUTHENTICATED")]
    #[Route("/todos/{id}", "update_todo", methods: ["PATCH", "PUT"])]
    public function updateTodo(Todo $todo, Request $request, TodoOptionsResolver $todoOptionsResolver, ValidatorInterface $validator, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $isPutMethode = $request->getMethod() === "PUT";
            $requestBody = json_decode($request->getContent(), true);

            $fields = $todoOptionsResolver
                ->configureTitle($isPutMethode)
                ->configureCompleted($isPutMethode)
                ->resolve($requestBody);

            foreach($fields as $field => $value) {
                switch($field) {
                    case "title":
                        $todo->setTitle($value);
                        break;
                    case "completed":
                        $todo->setCompleted($value);
                        break;
                }
            }

            $errors = $validator->validate($todo);
            if (count($errors) > 0) {
                throw new InvalidArgumentException((string) $errors);
            }

            $entityManager->flush();

            return $this->json($todo);

        } catch (Exception $exception) {
            throw new BadRequestHttpException($exception->getMessage());
        }
    }

    #[IsGranted("IS_AUTHENTICATED")]
    #[Route("/todos/{id}", "delete_todo", methods: ["DELETE"])]
    public function deleteTodo(Todo $todo, TodoRepository $todoRepository): JsonResponse
    {
        $todoRepository->remove($todo, true);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
