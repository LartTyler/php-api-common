<?php
	namespace DaybreakStudios\RestApiCommon\Payload\Decoders;

	use DaybreakStudios\RestApiCommon\Error\Errors\Validation\ValidationFailedError;
	use DaybreakStudios\RestApiCommon\Exceptions\ApiErrorException;
	use DaybreakStudios\RestApiCommon\Payload\DecoderParseContext;
	use DaybreakStudios\RestApiCommon\Payload\DecoderIntent;
	use DaybreakStudios\RestApiCommon\Payload\Exceptions\PayloadDecoderException;
	use DaybreakStudios\RestApiCommon\Payload\PayloadDecoderInterface;
	use Symfony\Component\Serializer\SerializerInterface;
	use Symfony\Component\Validator\Constraint;
	use Symfony\Component\Validator\Validator\ValidatorInterface;

	class SymfonyDeserializeDecoder implements PayloadDecoderInterface {
		public const GROUP_CREATE = 'create';
		public const GROUP_UPDATE = 'update';

		/**
		 * Indicates the fully-qualified class name that should be passed to {@see SerializerInterface::deserialize()}
		 * when parsing the input payload
		 *
		 * This context variable is required.
		 */
		public const CONTEXT_PAYLOAD_CLASS = 'symfony_deserializer.payload_class';

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
		 * @var string[]
		 */
		protected $createGroups = [];

		/**
		 * @var string[]
		 */
		protected $updateGroups = [];

		/**
		 * @var array
		 */
		protected $deserializeContext = [];

		/**
		 * SymfonyDeserializeDecoder constructor.
		 *
		 * @param SerializerInterface     $serializer
		 * @param string                  $defaultFormat
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

			if ($validator) {
				$this->createGroups = [
					Constraint::DEFAULT_GROUP,
					static::GROUP_CREATE,
				];

				$this->updateGroups = [
					Constraint::DEFAULT_GROUP,
					static::GROUP_UPDATE,
				];
			}
		}

		/**
		 * {@inheritdoc}
		 *
		 * @see SymfonyDeserializeDecoder::CONTEXT_PAYLOAD_CLASS
		 */
		public function parse(string $intent, string $input, array $context = []): object {
			$payloadClass = $context[static::CONTEXT_PAYLOAD_CLASS] ?? null;

			assert(
				is_string($payloadClass) && class_exists($payloadClass),
				sprintf('%s requires the "%s" context variable', static::class, static::CONTEXT_PAYLOAD_CLASS)
			);

			$payload = $this->serializer->deserialize(
				$input,
				$payloadClass,
				$context[DecoderParseContext::INPUT_FORMAT] ?? $this->getDefaultFormat(),
				$this->getDeserializeContext()
			);

			if ($this->validator) {
				if ($intent === DecoderIntent::CREATE)
					$groups = $this->getCreateGroups();
				else if ($intent === DecoderIntent::UPDATE)
					$groups = $this->getUpdateGroups();
				else
					throw PayloadDecoderException::invalidIntent($intent);

				$failures = $this->validator->validate($payload, null, $groups);

				if ($failures->count())
					throw new ApiErrorException(new ValidationFailedError($failures));
			}

			return $payload;
		}

		/**
		 * @return string
		 */
		public function getDefaultFormat(): string {
			return $this->defaultFormat;
		}

		/**
		 * @param string $defaultFormat
		 *
		 * @return $this
		 */
		public function setDefaultFormat(string $defaultFormat) {
			$this->defaultFormat = $defaultFormat;

			return $this;
		}

		/**
		 * @return string[]
		 */
		public function getCreateGroups(): array {
			return $this->createGroups;
		}

		/**
		 * @param string[] $createGroups
		 *
		 * @return $this
		 */
		public function setCreateGroups(array $createGroups) {
			$this->createGroups = $createGroups;

			return $this;
		}

		/**
		 * @return string[]
		 */
		public function getUpdateGroups(): array {
			return $this->updateGroups;
		}

		/**
		 * @param string[] $updateGroups
		 *
		 * @return $this
		 */
		public function setUpdateGroups(array $updateGroups) {
			$this->updateGroups = $updateGroups;

			return $this;
		}

		/**
		 * @return array
		 */
		public function getDeserializeContext(): array {
			return $this->deserializeContext;
		}

		/**
		 * @param array $deserializeContext
		 *
		 * @return $this
		 */
		public function setDeserializeContext(array $deserializeContext) {
			$this->deserializeContext = $deserializeContext;

			return $this;
		}
	}