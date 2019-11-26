<?php
	namespace DaybreakStudios\RestApiCommon\Error\Errors\DoctrineQueryDocument;

	use DaybreakStudios\RestApiCommon\Error\ApiError;
	use Symfony\Component\HttpFoundation\Response;

	class EmptyQueryError extends ApiError {
		/**
		 * EmptyQueryError constructor.
		 *
		 * @param int|null $httpStatus
		 */
		public function __construct(?int $httpStatus = Response::HTTP_BAD_REQUEST) {
			parent::__construct('invalid_query.empty', 'Your query object is empty', $httpStatus);
		}
	}