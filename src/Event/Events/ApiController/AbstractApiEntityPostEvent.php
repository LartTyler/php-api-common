<?php
	namespace DaybreakStudios\RestApiCommon\Event\Events\ApiController;

	abstract class AbstractApiEntityPostEvent extends AbstractApiEntityEvent {
		/**
		 * @var bool
		 */
		protected bool $shouldFlush = false;

		/**
		 * @return bool
		 */
		public function getShouldFlush(): bool {
			return $this->shouldFlush;
		}

		/**
		 * @param bool $shouldFlush
		 */
		public function setShouldFlush(bool $shouldFlush): void {
			$this->shouldFlush = $shouldFlush;
		}
	}
