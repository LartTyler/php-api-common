<?php
	namespace DaybreakStudios\RestApiCommon\Payload\Exceptions;

	class PayloadDecoderException extends \RuntimeException {
		/**
		 * @param string $errorMessage
		 *
		 * @return static
		 */
		public static function invalidJsonPayload(string $errorMessage): self {
			return new self('Could not parse JSON input: ' . $errorMessage);
		}

		/**
		 * @param string $value
		 *
		 * @return static
		 */
		public static function invalidIntent(string $value): self {
			return new self('Unknown parse intent "' . $value . '"');
		}
	}