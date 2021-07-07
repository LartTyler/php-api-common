<?php
	namespace DaybreakStudios\RestApiCommon\Event\Events\ApiController;

	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;

	class ApiEntityPostCloneEvent extends AbstractApiEntityPostEvent {
		/**
		 * @var EntityInterface
		 */
		protected EntityInterface $clonedEntity;

		/**
		 * ApiEntityPostCloneEvent constructor.
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
