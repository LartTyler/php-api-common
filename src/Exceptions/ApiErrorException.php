<?php
	namespace DaybreakStudios\RestApiCommon\Exceptions;

	use DaybreakStudios\RestApiCommon\Error\ApiErrorInterface;

	class ApiErrorException extends \RuntimeException {
		/**
		 * @var ApiErrorInterface
		 */
		protected ApiErrorInterface $error;

		/**
		 * ApiErrorException constructor.
		 *
		 * @param ApiErrorInterface $error
		 */
		public function __construct(ApiErrorInterface $error) {
			parent::__construct($error->getMessage());

			$this->error = $error;
		}

		/**
		 * @return ApiErrorInterface
		 */
		public function getApiError(): ApiErrorInterface {
			return $this->error;
		}
	}