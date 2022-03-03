<?php
	namespace DaybreakStudios\RestApiCommon;

	use DaybreakStudios\RestApiCommon\Error\ApiErrorInterface;
	use Symfony\Component\HttpFoundation\RequestStack;
	use Symfony\Component\HttpFoundation\Response;

	/**
	 * Wrapper class for a {@see ResponderInterface} that leverages Symfony components to infer certain response
	 * attributes, such as intended format.
	 *
	 * Generally most useful as a Symfony service. thus the name.
	 *
	 * @package DaybreakStudios\RestApiCommon
	 */
	class ResponderService {
		/**
		 * @var ResponderInterface
		 */
		protected ResponderInterface $responder;

		/**
		 * @var RequestStack
		 */
		protected RequestStack $requestStack;

		/**
		 * @var string
		 */
		protected string $defaultFormat;

		/**
		 * ResponderService constructor.
		 *
		 * @param ResponderInterface $responder
		 * @param RequestStack       $requestStack
		 * @param string             $defaultFormat
		 */
		public function __construct(
			ResponderInterface $responder,
			RequestStack $requestStack,
			string $defaultFormat = 'json'
		) {
			$this->responder = $responder;
			$this->requestStack = $requestStack;
			$this->defaultFormat = $defaultFormat;
		}

		/**
		 * @return ResponderInterface
		 */
		public function getResponder(): ResponderInterface {
			return $this->responder;
		}

		/**
		 * @return string
		 */
		public function getDefaultFormat(): string {
			return $this->defaultFormat;
		}

		/**
		 * @param mixed    $data
		 * @param int|null $status
		 * @param array    $headers
		 * @param array    $context
		 *
		 * @return Response
		 * @see ResponderInterface::createResponse()
		 *
		 */
		public function createResponse(
			mixed $data = null,
			?int $status = null,
			array $headers = [],
			array $context = []
		): Response {
			return $this->responder->createResponse(
				$this->getCurrentRequestFormat(),
				$data,
				$status,
				$headers,
				$context
			);
		}

		/**
		 * @param ApiErrorInterface $error
		 * @param int|null          $status
		 * @param array             $headers
		 * @param array             $context
		 *
		 * @return Response
		 * @see ResponderInterface::createErrorResponse()
		 *
		 */
		public function createErrorResponse(
			ApiErrorInterface $error,
			?int $status = null,
			array $headers = [],
			array $context = []
		): Response {
			return $this->responder->createErrorResponse(
				$error,
				$this->getCurrentRequestFormat(),
				$status,
				$headers,
				$context
			);
		}

		/**
		 * @return string
		 */
		protected function getCurrentRequestFormat(): string {
			return $this->requestStack->getCurrentRequest()->getRequestFormat($this->defaultFormat);
		}
	}
