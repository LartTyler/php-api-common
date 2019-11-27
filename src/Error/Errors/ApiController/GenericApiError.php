<?php
	namespace DaybreakStudios\RestApiCommon\Error\Errors\ApiController;

	use DaybreakStudios\RestApiCommon\Error\ApiError;

	class GenericApiError extends ApiError {
		/**
		 * GenericApiError constructor.
		 *
		 * @param string $message
		 */
		public function __construct(string $message) {
			parent::__construct('generic', $message);
		}
	}