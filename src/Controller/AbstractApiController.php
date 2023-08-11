<?php
	namespace DaybreakStudios\RestApiCommon\Controller;

	use DaybreakStudios\DoctrineQueryDocument\Projection\Projection;
	use DaybreakStudios\DoctrineQueryDocument\Projection\ProjectionInterface;
	use DaybreakStudios\DoctrineQueryDocument\QueryManagerInterface;
	use DaybreakStudios\RestApiCommon\Error\ApiErrorInterface;
	use DaybreakStudios\RestApiCommon\Error\Errors\ApiController\GenericApiError;
	use DaybreakStudios\RestApiCommon\Error\Errors\ApiController\PayloadRequiredError;
	use DaybreakStudios\RestApiCommon\Error\Errors\DoctrineQueryDocument\EmptyQueryError;
	use DaybreakStudios\RestApiCommon\Error\Errors\DoctrineQueryDocument\ProjectionSyntaxError;
	use DaybreakStudios\RestApiCommon\Error\Errors\DoctrineQueryDocument\QuerySyntaxError;
	use DaybreakStudios\RestApiCommon\Error\Errors\NotFoundError;
	use DaybreakStudios\RestApiCommon\Error\Errors\Validation\ValidationFailedError;
	use DaybreakStudios\RestApiCommon\Event\Events\ApiController\ApiEntityCloneEvent;
	use DaybreakStudios\RestApiCommon\Event\Events\ApiController\ApiEntityCreateEvent;
	use DaybreakStudios\RestApiCommon\Event\Events\ApiController\ApiEntityDeleteEvent;
	use DaybreakStudios\RestApiCommon\Event\Events\ApiController\ApiEntityPostCloneEvent;
	use DaybreakStudios\RestApiCommon\Event\Events\ApiController\ApiEntityPostCreateEvent;
	use DaybreakStudios\RestApiCommon\Event\Events\ApiController\ApiEntityPostDeleteEvent;
	use DaybreakStudios\RestApiCommon\Event\Events\ApiController\ApiEntityPostUpdateEvent;
	use DaybreakStudios\RestApiCommon\Event\Events\ApiController\ApiEntityUpdateEvent;
	use DaybreakStudios\RestApiCommon\Exceptions\ApiErrorException;
	use DaybreakStudios\RestApiCommon\Payload\DecoderIntent;
	use DaybreakStudios\RestApiCommon\Payload\Decoders\SimpleJsonPayloadDecoder;
	use DaybreakStudios\RestApiCommon\Payload\Exceptions\PayloadDecoderException;
	use DaybreakStudios\RestApiCommon\Payload\PayloadDecoderInterface;
	use DaybreakStudios\RestApiCommon\ResponderService;
	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;
	use DaybreakStudios\Utility\EntityTransformers\CloneableEntityTransformerInterface;
	use DaybreakStudios\Utility\EntityTransformers\EntityTransformerInterface;
	use DaybreakStudios\Utility\EntityTransformers\Exceptions\ConstraintViolationException;
	use DaybreakStudios\Utility\EntityTransformers\Exceptions\EntityCloneException;
	use DaybreakStudios\Utility\EntityTransformers\Exceptions\EntityTransformerException;
	use Doctrine\ORM\EntityManagerInterface;
	use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\HttpFoundation\Response;
	use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

	abstract class AbstractApiController extends AbstractController {
		/**
		 * @var QueryManagerInterface
		 */
		protected QueryManagerInterface $queryManager;

		/**
		 * @var string
		 */
		protected string $entityClass;

		/**
		 * @var PayloadDecoderInterface
		 */
		protected PayloadDecoderInterface $payloadDecoder;

		/**
		 * @var EntityManagerInterface
		 */
		protected EntityManagerInterface $entityManager;

		/**
		 * @var ResponderService
		 */
		protected ResponderService $responder;

		/**
		 * @var EventDispatcherInterface|null
		 */
		protected ?EventDispatcherInterface $eventDispatcher = null;

		/**
		 * AbstractApiController constructor.
		 *
		 * @param QueryManagerInterface        $queryManager
		 * @param string                       $entityClass
		 * @param PayloadDecoderInterface|null $payloadDecoder
		 */
		public function __construct(
			QueryManagerInterface $queryManager,
			string $entityClass,
			?PayloadDecoderInterface $payloadDecoder = null
		) {
			if (!is_a($entityClass, EntityInterface::class, true))
				throw new \InvalidArgumentException($entityClass . ' must implement ' . EntityInterface::class);

			$this->queryManager = $queryManager;
			$this->entityClass = $entityClass;
			$this->payloadDecoder = $payloadDecoder ?? new SimpleJsonPayloadDecoder();
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
		 * @required
		 *
		 * @param EventDispatcherInterface $eventDispatcher
		 *
		 * @return void
		 */
		public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void {
			$this->eventDispatcher = $eventDispatcher;
		}

		/**
		 * @param EntityTransformerInterface $transformer
		 * @param Request                    $request
		 *
		 * @return Response
		 */
		protected function doCreate(EntityTransformerInterface $transformer, Request $request): Response {
			try {
				$payload = $this->payloadDecoder->parse(DecoderIntent::CREATE, $request->getContent());
			} catch (PayloadDecoderException | ApiErrorException $exception) {
				return $this->handleCrudException($request, $exception);
			}

			try {
				$entity = $transformer->create($payload);
			} catch (EntityTransformerException | ApiErrorException $exception) {
				return $this->handleCrudException($request, $exception);
			}

			if ($this->eventDispatcher !== null) {
				$event = new ApiEntityCreateEvent($entity, $payload);

				$this->eventDispatcher->dispatch($event);
			}

			$this->entityManager->flush();

			if ($this->eventDispatcher !== null) {
				$event = new ApiEntityPostCreateEvent($entity, $payload);
				$this->eventDispatcher->dispatch($event);

				if ($event->getShouldFlush())
					$this->entityManager->flush();
			}

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
			try {
				$payload = $this->payloadDecoder->parse(DecoderIntent::UPDATE, $request->getContent());
			} catch (PayloadDecoderException | ApiErrorException $exception) {
				return $this->handleCrudException($request, $exception);
			}

			try {
				$transformer->update($entity, $payload);
			} catch (EntityTransformerException | ApiErrorException $exception) {
				return $this->handleCrudException($request, $exception);
			}

			if ($this->eventDispatcher !== null) {
				$event = new ApiEntityUpdateEvent($entity, $payload);

				$this->eventDispatcher->dispatch($event);
			}

			$this->entityManager->flush();

			if ($this->eventDispatcher !== null) {
				$event = new ApiEntityPostUpdateEvent($entity, $payload);
				$this->eventDispatcher->dispatch($event);

				if ($event->getShouldFlush())
					$this->entityManager->flush();
			}

			return $this->respond($request, $entity);
		}

		/**
		 * @param CloneableEntityTransformerInterface $transformer
		 * @param EntityInterface                     $source
		 * @param Request                             $request
		 *
		 * @return Response
		 */
		protected function doClone(
			CloneableEntityTransformerInterface $transformer,
			EntityInterface $source,
			Request $request
		): Response {
			if ($request->getContent()) {
				try {
					$payload = $this->payloadDecoder->parse(DecoderIntent::CLONE, $request->getContent());
				} catch (PayloadDecoderException | ApiErrorException $exception) {
					return $this->handleCrudException($request, $exception);
				}
			} else
				$payload = null;

			try {
				$clonedEntity = $transformer->clone($source, $payload);
			} catch (EntityTransformerException | ApiErrorException $exception) {
				return $this->handleCrudException($request, $exception);
			} catch (EntityCloneException $exception) {
				return $this->respond($request, new PayloadRequiredError());
			}

			if ($this->eventDispatcher !== null) {
				$event = new ApiEntityCloneEvent($source, $clonedEntity);

				$this->eventDispatcher->dispatch($event);
			}

			$this->entityManager->flush();

			if ($this->eventDispatcher !== null) {
				$event = new ApiEntityPostCloneEvent($source, $clonedEntity);
				$this->eventDispatcher->dispatch($event);

				if ($event->getShouldFlush())
					$this->entityManager->flush();
			}

			return $this->respond($request, $clonedEntity);
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

			if ($this->eventDispatcher !== null) {
				$event = new ApiEntityPostDeleteEvent($entity);
				$this->eventDispatcher->dispatch($event);

				if ($event->getShouldFlush())
					$this->entityManager->flush();
			}

			return new Response('', Response::HTTP_NO_CONTENT);
		}

		/**
		 * @param Request $request
		 * @param array   $queryOverrides
		 *
		 * @return Response
		 */
		protected function doList(Request $request, array $queryOverrides = []): Response {
			if (!$queryOverrides && !$request->query->has('q'))
				return $this->respond($request, $this->entityManager->getRepository($this->entityClass)->findAll());

			$queryBuilder = $this->entityManager->createQueryBuilder()
				->from($this->entityClass, 'e')
				->select('e');

			$limit = $request->query->get('limit');

			if (is_numeric($limit))
				$queryBuilder->setMaxResults((int)$limit);

			$offset = $request->query->get('offset');

			if (is_numeric($offset))
				$queryBuilder->setFirstResult((int)$offset);

			if ($request->query->has('q')) {
				$query = @json_decode($request->query->get('q'), true);

				if (json_last_error() !== JSON_ERROR_NONE)
					return $this->respond($request, new QuerySyntaxError());
				else if (!$query)
					return $this->respond($request, new EmptyQueryError());
			} else
				$query = [];

			try {
				$this->queryManager->apply($queryBuilder, $queryOverrides + $query);
			} catch (\Exception $exception) {
				return $this->respond($request, new GenericApiError($exception->getMessage()));
			}

			return $this->respond($request, $queryBuilder->getQuery()->getResult());
		}

		/**
		 * @param Request $request
		 * @param mixed   $data
		 *
		 * @return Response
		 */
		protected function respond(Request $request, mixed $data): Response {
			if ($data instanceof Response)
				return $data;
			else if ($data instanceof ApiErrorInterface || $data === null)
				return $this->responder->createErrorResponse($data ?? new NotFoundError());

			$fields = $request->query->get('p');

			if ($fields) {
				$fields = @json_decode($fields, true);

				if (json_last_error() !== JSON_ERROR_NONE)
					return $this->responder->createErrorResponse(new ProjectionSyntaxError());
			} else
				$fields = [];

			$projection = Projection::fromFields($fields);

			if (is_array($data))
				$data = $this->normalizeMany($data, $projection);
			else if ($data instanceof EntityInterface)
				$data = $projection->filter($this->normalizeOne($data, $projection));

			return $this->responder->createResponse($data);
		}

		protected function normalizeMany(array $data, ProjectionInterface $projection): array {
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
		 * @param Request    $request
		 * @param \Exception $exception
		 *
		 * @return Response
		 */
		protected function handleCrudException(Request $request, \Exception $exception): Response {
			if ($exception instanceof ConstraintViolationException)
				$error = new ValidationFailedError($exception->getErrors());
			else if ($exception instanceof ApiErrorException)
				$error = $exception->getApiError();
			else
				$error = new GenericApiError($exception->getMessage());

			return $this->respond($request, $error);
		}

		protected abstract function normalizeOne(EntityInterface $entity, ProjectionInterface $projection): array;
	}
