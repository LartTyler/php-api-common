<?php
	namespace DaybreakStudios\RestApiCommon\Event\Events\ApiController;

	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;

	class ApiEntityCloneEvent extends AbstractApiEntityEvent {
		/**
		 * @var EntityInterface
		 */
		protected EntityInterface $clonedEntity;

		/**
		 * ApiEntityCloneEvent constructor.
		 *
		 * @param EntityInterface $source
		 * @param EntityInterface $clonedEntity
		 */
		public function __construct(EntityInterface $source, EntityInterface $clonedEntity) {
			parent::__construct($source);

			$this->clonedEntity = $clonedEntity;
		}

		/**
		 * @return EntityInterface
		 */
		public function getClonedEntity(): EntityInterface {
			return $this->clonedEntity;
		}
	}