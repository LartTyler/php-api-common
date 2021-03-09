<?php
	namespace DaybreakStudios\RestApiCommon\Error\Errors\ApiController;

	use DaybreakStudios\RestApiCommon\Error\ApiError;

	class PayloadRequiredError extends ApiError {
		/**
		 * PayloadRequiredError constructor.
		 */
		public function __construct() {
			parent::__construct('payload_required', 'You must include a body when requesting this endpoint');
		}
	}