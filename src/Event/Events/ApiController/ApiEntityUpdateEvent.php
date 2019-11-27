<?php
	namespace DaybreakStudios\RestApiCommon\Event\Events\ApiController;

	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;

	class ApiEntityUpdateEvent extends AbstractApiEntityEvent {
		/**
		 * @var object
		 */
		protected $payload;

		/**
		 * ApiEntityUpdateEvent constructor.
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