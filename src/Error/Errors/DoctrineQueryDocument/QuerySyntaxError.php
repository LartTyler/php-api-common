<?php
	namespace DaybreakStudios\RestApiCommon\Error\Errors\DoctrineQueryDocument;

	use DaybreakStudios\RestApiCommon\Error\ApiError;
	use Symfony\Component\HttpFoundation\Response;

	class QuerySyntaxError extends ApiError {
		/**
		 * QuerySyntaxError constructor.
		 *
		 * @param string|null $error
		 * @param int|null    $httpStatus
		 */
		public function __construct(?string $error = null, ?int $httpStatus = Response::HTTP_BAD_REQUEST) {
			$message = 'Your query object is invalid; ' . ($error ?? 'check your syntax and try again');

			parent::__construct('invalid_query.syntax', $error, $httpStatus);
		}
	}