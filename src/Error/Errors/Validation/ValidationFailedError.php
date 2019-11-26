<?php
	namespace DaybreakStudios\RestApiCommon\Error\Errors\Validation;

	use DaybreakStudios\RestApiCommon\Error\ApiError;
	use Symfony\Component\HttpFoundation\Response;
	use Symfony\Component\Validator\ConstraintViolationInterface;
	use Symfony\Component\Validator\ConstraintViolationListInterface;

	class ValidationFailedError extends ApiError {
		/**
		 * ValidationConstraintError constructor.
		 *
		 * @param ConstraintViolationListInterface $violations
		 * @param string|null                      $message
		 * @param int                              $httpStatus
		 */
		public function __construct(
			ConstraintViolationListInterface $violations,
			?string $message = null,
			int $httpStatus = Response::HTTP_BAD_REQUEST
		) {
			$normalized = [];

			/** @var ConstraintViolationInterface $violation */
			foreach ($violations as $violation) {
				$normalized[$violation->getPropertyPath()] = [
					'code' => $violation->getCode(),
					'path' => $violation->getPropertyPath(),
					'message' => $violation->getMessage(),
				];
			}

			parent::__construct(
				'validation_failed',
				$message ?? 'One or more fields did not pass validation',
				$httpStatus
			);

			$this->setContext(
				[
					'failures' => $normalized,
				]
			);
		}
	}