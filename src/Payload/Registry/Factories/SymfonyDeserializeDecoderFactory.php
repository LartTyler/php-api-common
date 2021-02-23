<?php
	namespace DaybreakStudios\RestApiCommon\Payload\Registry\Factories;

	use DaybreakStudios\RestApiCommon\Payload\Decoders\SymfonyDeserializeDecoder;
	use DaybreakStudios\RestApiCommon\Payload\PayloadDecoderInterface;
	use DaybreakStudios\RestApiCommon\Payload\Registry\PayloadDecoderFactoryInterface;
	use Symfony\Component\Serializer\SerializerInterface;
	use Symfony\Component\Validator\Validator\ValidatorInterface;

	class SymfonyDeserializeDecoderFactory implements PayloadDecoderFactoryInterface {
		/**
		 * @var SerializerInterface
		 */
		protected $serializer;

		/**
		 * @var string
		 */
		protected $defaultFormat;

		/**
		 * @var ValidatorInterface|null
		 */
		protected $validator;

		/**
		 * SymfonyDeserializeDecoderFactory constructor.
		 *
		 * @param SerializerInterface $serializer
		 * @param string $defaultFormat
		 * @param ValidatorInterface|null $validator
		 */
		public function __construct(
			SerializerInterface $serializer,
			string $defaultFormat,
			ValidatorInterface $validator = null
		) {
			$this->serializer = $serializer;
			$this->defaultFormat = $defaultFormat;
			$this->validator = $validator;
		}

		/**
		 * {@inheritdoc}
		 */
		public function create(string $dtoClass): PayloadDecoderInterface {
			return new SymfonyDeserializeDecoder($this->serializer, $this->defaultFormat, $dtoClass, $this->validator);
		}
	}