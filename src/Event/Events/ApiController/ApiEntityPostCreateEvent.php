<?php
	namespace DaybreakStudios\RestApiCommon\Event\Events\ApiController;

	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;

	class ApiEntityPostCreateEvent extends AbstractApiEntityPostEvent {
		/**
		 * @var object
		 */
		protected object $payload;

		/**
		 * ApiEntityPostCreateEvent constructor.
		 *
		 * @param EntityInterface $entity
		 * @param object          $payload
		 */
		public function __construct(EntityInterface $entity, object $payload) {
			parent::__construct($entity);

			$this->payload = $payload;
		}

		/**
		 * @return object
		 */
		public function getPayload(): object {
			return $this->payload;
		}
	}
