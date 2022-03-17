<?php
	namespace DaybreakStudios\RestApiCommon\Event\Events\ApiController;

	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;

	class ApiEntityPostUpdateEvent extends AbstractApiEntityPostEvent {
		public function __construct(EntityInterface $entity, protected object $payload) {
			parent::__construct($entity);
		}

		/**
		 * @return object
		 */
		public function getPayload(): object {
			return $this->payload;
		}
	}
