<?php
	namespace DaybreakStudios\RestApiCommon\Payload;

	interface PayloadDecoderInterface {
		/**
		 * @param string      $intent
		 * @param string      $input
		 * @param string|null $format
		 *
		 * @return object
		 * @see DecoderIntent
		 */
		public function parse(string $intent, string $input, ?string $format = null): object;
	}