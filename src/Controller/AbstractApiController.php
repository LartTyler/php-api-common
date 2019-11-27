<?php
	namespace DaybreakStudios\RestApiCommon\Controller;

	use DaybreakStudios\DoctrineQueryDocument\Projection\Projection;
	use DaybreakStudios\DoctrineQueryDocument\QueryManagerInterface;
	use DaybreakStudios\RestApiCommon\Error\ApiErrorInterface;
	use DaybreakStudios\RestApiCommon\Error\Errors\ApiController\GenericApiError;
	use DaybreakStudios\RestApiCommon\Error\Errors\ApiController\InvalidPayloadError;
	use DaybreakStudios\RestApiCommon\Error\Errors\DoctrineQueryDocument\EmptyQueryError;
	use DaybreakStudios\RestApiCommon\Error\Errors\DoctrineQueryDocument\ProjectionSyntaxError;
	use DaybreakStudios\RestApiCommon\Error\Errors\DoctrineQueryDocument\QuerySyntaxError;
	use DaybreakStudios\RestApiCommon\Error\Errors\NotFoundError;
	use DaybreakStudios\RestApiCommon\Error\Errors\Validation\ValidationFailedError;
	use DaybreakStudios\RestApiCommon\Event\Events\ApiController\ApiEntityCreateEvent;
	use DaybreakStudios\RestApiCommon\Event\Events\ApiController\ApiEntityDeleteEvent;
	use DaybreakStudios\RestApiCommon\Event\Events\ApiController\ApiEntityUpdateEvent;
	use DaybreakStudios\RestApiCommon\ResponderService;
	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;
	use DaybreakStudios\Utility\EntityTransformers\EntityTransformerInterface;
	use DaybreakStudios\Utility\EntityTransformers\Exceptions\ConstraintViolationException;
	use DaybreakStudios\Utility\EntityTransformers\Exceptions\EntityTransformerException;
	use Doctrine\ORM\EntityManagerInterface;
	use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
	use Symfony\Component\EventDispatcher\EventDispatcher;
	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\HttpFoundation\Response;

	abstract class AbstractApiController extends AbstractController {
		/**
		 * @var string
		 */
		protected $entityClass;

		/**
		 * @var QueryManagerInterface
		 */
		protected $queryManager;

		/**
		 * @var EntityManagerInterface
		 */
		protected $entityManager;

		/**
		 * @var ResponderService
		 */
		protected $responder;

		/**
		 * @var EventDispatcher|null
		 */
		protected $eventDispatcher = null;

		/**
		 * AbstractApiController constructor.
		 *
		 * @param QueryManagerInterface $queryManager
		 * @param string                $entityClass
		 */
		public function __construct(QueryManagerInterface $queryManager, string $entityClass) {
			if (!is_a($entityClass, EntityInterface::class, true))
				throw new \InvalidArgumentException($entityClass . ' must implement ' . EntityInterface::class);

			$this->queryManager = $queryManager;
			$this->entityClass = $entityClass;
		}

		/**
		 * @required
		 *
		 * @param EntityManagerInterface $entityManager
		 *
		 * @return void
		 */
		public function setEntityManager(EntityManagerInterface $entityManager): void {
			$this->entityManager = $entityManager;
		}

		/**
		 * @required
		 *
		 * @param ResponderService $responder
		 *
		 * @return void
		 */
		public function setResponderService(ResponderService $responder): void {
			$this->responder = $responder;
		}

		/**
		 * @param EntityTransformerInterface $transformer
		 * @param Request                    $request
		 *
		 * @return Response
		 */
		protected function doCreate(EntityTransformerInterface $transformer, Request $request): Response {
			$payload = @json_decode($request->getContent());

			if (json_last_error() !== JSON_ERROR_NONE)
				return $this->respond($request, new InvalidPayloadError());

			try {
				$entity = $transformer->create($payload);
			} catch (EntityTransformerException $exception) {
				if ($exception instanceof ConstraintViolationException)
					$error = new ValidationFailedError($exception->getErrors());
				else
					$error = new GenericApiError($exception->getMessage());

				return $this->respond($request, $error);
			}

			if ($this->eventDispatcher !== null) {
				$event = new ApiEntityCreateEvent($entity, $payload);

				$this->eventDispatcher->dispatch($event);
			}

			$this->entityManager->flush();

			return $this->respond($request, $entity);
		}

		/**
		 * @param EntityTransformerInterface $transformer
		 * @param EntityInterface            $entity
		 * @param Request                    $request
		 *
		 * @return Response
		 */
		protected function doUpdate(
			EntityTransformerInterface $transformer,
			EntityInterface $entity,
			Request $request
		): Response {
			$payload = @json_decode($request->getContent());

			if (json_last_error() !== JSON_ERROR_NONE)
				return $this->respond($request, new InvalidPayloadError());

			try {
				$transformer->update($entity, $payload);
			} catch (EntityTransformerException $exception) {
				if ($exception instanceof ConstraintViolationException)
					$error = new ValidationFailedError($exception->getErrors());
				else
					$error = new GenericApiError($exception->getMessage());

				return $this->respond($request, $error);
			}

			if ($this->eventDispatcher !== null) {
				$event = new ApiEntityUpdateEvent($entity, $payload);

				$this->eventDispatcher->dispatch($event);
			}

			$this->entityManager->flush();

			return $this->respond($request, $entity);
		}

		/**
		 * @param EntityTransformerInterface $transformer
		 * @param EntityInterface            $entity
		 *
		 * @return Response
		 */
		protected function doDelete(EntityTransformerInterface $transformer, EntityInterface $entity): Response {
			$transformer->delete($entity);

			if ($this->eventDispatcher !== null) {
				$event = new ApiEntityDeleteEvent($entity);

				$this->eventDispatcher->dispatch($event);
			}

			$this->entityManager->flush();

			return new Response('', Response::HTTP_NO_CONTENT);
		}

		/**
		 * @param Request $request
		 *
		 * @return Response
		 */
		protected function doList(Request $request): Response {
			if ($request->query->has('q')) {
				$queryBuilder = $this->entityManager->createQueryBuilder()
					->from($this->entityClass, 'e')
					->select('e');

				$limit = $request->query->get('limit');

				if (is_numeric($limit))
					$queryBuilder->setMaxResults((int)$limit);

				$offset = $request->query->get('offset');

				if (is_numeric($offset))
					$queryBuilder->setFirstResult((int)$offset);

				$query = @json_decode($request->query->get('q'), true);

				if (json_last_error() !== JSON_ERROR_NONE)
					return $this->respond($request, new QuerySyntaxError());
				else if (!$query)
					return $this->respond($request, new EmptyQueryError());

				try {
					$this->queryManager->apply($queryBuilder, $query);
				} catch (\Exception $exception) {
					return $this->respond($request, new GenericApiError($exception->getMessage()));
				}

				$results = $queryBuilder->getQuery()->getResult();
			} else
				$results = $this->entityManager->getRepository($this->entityClass)->findAll();

			return $this->respond($request, $results);
		}

		/**
		 * @param Request $request
		 * @param mixed   $data
		 *
		 * @return Response
		 */
		protected function respond(Request $request, $data): Response {
			if ($data instanceof Response)
				return $data;
			else if ($data instanceof ApiErrorInterface || $data === null)
				return $this->responder->createErrorResponse($data ?? new NotFoundError());

			$fields = $request->query->get('p', []);

			if ($fields) {
				$fields = @json_decode($fields, true);

				if (json_last_error() !== JSON_ERROR_NONE)
					return $this->responder->createErrorResponse(new ProjectionSyntaxError());
			}

			$projection = Projection::fromFields($fields);

			if (is_array($data))
				$data = $this->normalizeMany($data, $projection);
			else if ($data instanceof EntityInterface)
				$data = $this->normalizeOne($data, $projection);

			return $this->responder->createResponse($data);
		}

		/**
		 * @param array      $data
		 * @param Projection $projection
		 *
		 * @return array
		 */
		protected function normalizeMany(array $data, Projection $projection): array {
			$normalized = [];

			foreach ($data as $item) {
				if ($item instanceof EntityInterface)
					$normalized[] = $projection->filter($this->normalizeOne($item, $projection));
				else
					$normalized[] = $item;
			}

			return $normalized;
		}

		/**
		 * @param EntityInterface $entity
		 * @param Projection      $projection
		 *
		 * @return array
		 */
		protected abstract function normalizeOne(EntityInterface $entity, Projection $projection): array;
	}