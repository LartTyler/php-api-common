<?php
	namespace DaybreakStudios\RestApiCommon\Payload\Registry;

	use DaybreakStudios\RestApiCommon\Payload\PayloadDecoderInterface;

	interface PayloadDecoderRegistryInterface {
		/**
		 * @param string $dtoClass
		 *
		 * @return PayloadDecoderInterface
		 */
		public function getDecoder(string $dtoClass): PayloadDecoderInterface;
	}