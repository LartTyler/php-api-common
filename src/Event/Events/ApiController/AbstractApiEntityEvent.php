<?php
	namespace DaybreakStudios\RestApiCommon\Event\Events\ApiController;

	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;
	use Symfony\Contracts\EventDispatcher\Event;

	abstract class AbstractApiEntityEvent extends Event {
		/**
		 * @var EntityInterface
		 */
		protected EntityInterface $entity;

		/**
		 * AbstractApiEntityEvent constructor.
		 *
		 * @param EntityInterface $entity
		 */
		public function __construct(EntityInterface $entity) {
			$this->entity = $entity;
		}

		/**
		 * @return EntityInterface
		 */
		public function getEntity(): EntityInterface {
			return $this->entity;
		}
	}