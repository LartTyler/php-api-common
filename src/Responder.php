<?php
	namespace DaybreakStudios\RestApiCommon;

	use DaybreakStudios\RestApiCommon\Error\ApiErrorInterface;
	use Symfony\Component\HttpFoundation\Response;
	use Symfony\Component\Serializer\SerializerInterface;

	class Responder implements ResponderInterface {
		/**
		 * @var SerializerInterface
		 */
		protected SerializerInterface $serializer;

		/**
		 * Responder constructor.
		 *
		 * @param SerializerInterface $serializer
		 */
		public function __construct(SerializerInterface $serializer) {
			$this->serializer = $serializer;
		}

		/**
		 * {@inheritdoc}
		 */
		public function createErrorResponse(
			ApiErrorInterface $error,
			string $format,
			?int $status = null,
			array $headers = [],
			array $context = []
		): Response {
			if ($status === null)
				$status = $error->getHttpStatus() ?? Response::HTTP_BAD_REQUEST;

			$data = [
				'error' => [
					'code' => $error->getCode(),
					'message' => $error->getMessage(),
				],
			];

			if ($error->getContext() !== null)
				$data['error']['context'] = $error->getContext();

			return $this->createResponse($format, $data, $status, $headers, $context);
		}

		/**
		 * {@inheritdoc}
		 */
		public function createResponse(
			string $format,
			$data = null,
			?int $status = null,
			array $headers = [],
			array $context = []
		): Response {
			if ($data === null && $status === null)
				$status = Response::HTTP_NO_CONTENT;
			else if ($data !== null)
				$data = $this->serializer->serialize($data, $format, $context);

			return new Response(
				$data,
				$status ?? Response::HTTP_OK,
				$headers + [
					'Content-Type' => 'application/' . $format,
				]
			);
		}
	}