<?php
	namespace DaybreakStudios\RestApiCommon\Payload;

	interface PayloadDecoderInterface {
		/**
		 * @param string $intent
		 * @param string $input
		 * @param array  $context
		 *
		 * @return object
		 * @see DecoderIntent
		 */
		public function parse(string $intent, string $input, array $context = []): object;
	}