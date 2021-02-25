<?php
	namespace DaybreakStudios\RestApiCommon\Payload\Registry;

	use DaybreakStudios\RestApiCommon\Payload\PayloadDecoderInterface;

	class PayloadDecoderRegistry implements PayloadDecoderRegistryInterface {
		/**
		 * @var PayloadDecoderFactoryInterface
		 */
		protected PayloadDecoderFactoryInterface $decoderFactory;

		/**
		 * @var PayloadDecoderInterface[]
		 */
		protected array $decoders = [];

		public function __construct(PayloadDecoderFactoryInterface $decoderFactory) {
			$this->decoderFactory = $decoderFactory;
		}

		/**
		 * {@inheritdoc}
		 */
		public function getDecoder(string $dtoClass): PayloadDecoderInterface {
			return $this->decoders[$dtoClass] ?? $this->decoders[$dtoClass] = $this->decoderFactory->create($dtoClass);
		}
	}