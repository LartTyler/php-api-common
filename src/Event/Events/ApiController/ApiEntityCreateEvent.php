<?php
	namespace DaybreakStudios\RestApiCommon\Event\Events\ApiController;

	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;

	class ApiEntityCreateEvent extends AbstractApiEntityEvent {
		/**
		 * @var object
		 */
		protected object $payload;

		/**
		 * ApiEntityCreateEvent constructor.
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