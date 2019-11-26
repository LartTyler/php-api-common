<?php
	namespace DaybreakStudios\RestApiCommon\Error\Errors\DoctrineQueryDocument;

	use DaybreakStudios\RestApiCommon\Error\ApiError;
	use Symfony\Component\HttpFoundation\Response;

	class EmptyProjectionError extends ApiError {
		/**
		 * EmptyProjectionError constructor.
		 *
		 * @param int|null $httpStatus
		 */
		public function __construct(?int $httpStatus = Response::HTTP_BAD_REQUEST) {
			parent::__construct('invalid_projection.empty', 'Your projection object is empty', $httpStatus);
		}
	}