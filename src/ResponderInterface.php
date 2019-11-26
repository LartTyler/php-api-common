<?php
	namespace DaybreakStudios\RestApiCommon;

	use DaybreakStudios\RestApiCommon\Error\ApiErrorInterface;
	use Symfony\Component\HttpFoundation\Response;

	interface ResponderInterface {
		/**
		 * Creates a new response.
		 *
		 * If `$data` is `null`, no body content will be sent, and the 204 No Content HTTP status code will be set. If
		 * any value aside from `null` is used, it will be serialized prior to being set as the response body.
		 *
		 * @param string   $format  the response format (such as "json" or "xml")
		 * @param null     $data    the response data to set as the response body
		 * @param int|null $status  the HTTP status for the response
		 * @param array    $headers an array of headers to send; values provided here will take precedence over any
		 *                          default or inferred headers (such as Content-Type)
		 * @param array    $context an array containing context options for the serializer
		 *
		 * @return Response
		 */
		public function createResponse(
			string $format,
			$data = null,
			?int $status = null,
			array $headers = [],
			array $context = []
		): Response;

		/**
		 * Creates a new error response.
		 *
		 * @param ApiErrorInterface $error
		 * @param string            $format
		 * @param int|null          $status
		 * @param array             $headers
		 * @param array             $context
		 *
		 * @return Response
		 */
		public function createErrorResponse(
			ApiErrorInterface $error,
			string $format,
			?int $status = null,
			array $headers = [],
			array $context = []
		): Response;
	}