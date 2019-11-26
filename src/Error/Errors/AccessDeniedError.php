<?php
	namespace DaybreakStudios\RestApiCommon\Error\Errors;

	use DaybreakStudios\RestApiCommon\Error\ApiError;
	use Symfony\Component\HttpFoundation\Response;

	class AccessDeniedError extends ApiError {
		/**
		 * AccessDeniedError constructor.
		 *
		 * @param string|null $message
		 * @param int         $httpStatus
		 */
		public function __construct(?string $message = null, int $httpStatus = Response::HTTP_FORBIDDEN) {
			parent::__construct('access_denied', $message ?? 'Access Denied', $httpStatus);
		}
	}