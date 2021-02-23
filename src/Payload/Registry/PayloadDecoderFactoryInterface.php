<?php
	namespace DaybreakStudios\RestApiCommon\Payload\Registry;

	use DaybreakStudios\RestApiCommon\Payload\PayloadDecoderInterface;

	interface PayloadDecoderFactoryInterface {
		/**
		 * @param string $dtoClass
		 *
		 * @return PayloadDecoderInterface
		 */
		public function create(string $dtoClass): PayloadDecoderInterface;
	}