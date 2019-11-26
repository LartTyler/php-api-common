<?php
	namespace DaybreakStudios\RestApiCommon\Error\Errors;

	use DaybreakStudios\RestApiCommon\Error\ApiError;
	use Symfony\Component\HttpFoundation\Response;

	class NotFoundError extends ApiError {
		/**
		 * NotFoundError constructor.
		 *
		 * @param string|null $message
		 * @param int         $httpStatus
		 */
		public function __construct(?string $message = null, int $httpStatus = Response::HTTP_NOT_FOUND) {
			parent::__construct('not_found', $message ?? 'Not Found', $httpStatus);
		}
	}