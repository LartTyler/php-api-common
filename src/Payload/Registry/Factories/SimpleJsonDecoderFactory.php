<?php
	namespace DaybreakStudios\RestApiCommon\Payload\Registry\Factories;

	use DaybreakStudios\RestApiCommon\Payload\Decoders\SimpleJsonPayloadDecoder;
	use DaybreakStudios\RestApiCommon\Payload\PayloadDecoderInterface;
	use DaybreakStudios\RestApiCommon\Payload\Registry\PayloadDecoderFactoryInterface;

	class SimpleJsonDecoderFactory implements PayloadDecoderFactoryInterface {
		/**
		 * @var SimpleJsonPayloadDecoder
		 */
		protected SimpleJsonPayloadDecoder $decoder;

		/**
		 * SimpleJsonDecoderFactory constructor.
		 *
		 * @param int $depth
		 * @param int $flags
		 */
		public function __construct(int $depth = 512, int $flags = 0) {
			$this->decoder = new SimpleJsonPayloadDecoder($depth, $flags);
		}

		/**
		 * {@inheritdoc}
		 */
		public function create(string $dtoClass): PayloadDecoderInterface {
			return $this->decoder;
		}
	}