<?php
	namespace DaybreakStudios\RestApiCommon\Error\Errors\ApiController;

	use DaybreakStudios\RestApiCommon\Error\ApiError;

	class InvalidPayloadError extends ApiError {
		/**
		 * InvalidPayloadError constructor.
		 */
		public function __construct() {
			parent::__construct(
				'invalid_payload',
				'Could not decode request body; check that you\'re sending valid JSON'
			);
		}
	}