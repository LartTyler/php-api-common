<?php
	namespace DaybreakStudios\RestApiCommon\Payload\Decoders;

	use DaybreakStudios\RestApiCommon\Error\Errors\Validation\ValidationFailedError;
	use DaybreakStudios\RestApiCommon\Exceptions\ApiErrorException;
	use DaybreakStudios\RestApiCommon\Payload\DecoderIntent;
	use DaybreakStudios\RestApiCommon\Payload\Exceptions\PayloadDecoderException;
	use DaybreakStudios\RestApiCommon\Payload\PayloadDecoderInterface;
	use Symfony\Component\Serializer\SerializerInterface;
	use Symfony\Component\Validator\Constraint;
	use Symfony\Component\Validator\Validator\ValidatorInterface;

	class SymfonyDeserializeDecoder implements PayloadDecoderInterface {
		public const GROUP_CREATE = 'create';
		public const GROUP_UPDATE = 'update';
		public const GROUP_CLONE = 'clone';

		/**
		 * @var SerializerInterface
		 */
		protected SerializerInterface $serializer;

		/**
		 * @var string
		 */
		protected string $defaultFormat;

		/**
		 * @var string
		 */
		protected string $payloadClass;

		/**
		 * @var ValidatorInterface|null
		 */
		protected ?ValidatorInterface $validator;

		/**
		 * @var string[][]
		 */
		protected array $validatorGroups = [];

		/**
		 * @var array
		 */
		protected array $deserializeContext = [];

		/**
		 * SymfonyDeserializeDecoder constructor.
		 *
		 * @param SerializerInterface     $serializer
		 * @param string                  $defaultFormat
		 * @param string                  $payloadClass
		 * @param ValidatorInterface|null $validator
		 * @param string[][]|null         $validatorGroups
		 */
		public function __construct(
			SerializerInterface $serializer,
			string $defaultFormat,
			string $payloadClass,
			ValidatorInterface $validator = null,
			array $validatorGroups = null
		) {
			$this->serializer = $serializer;
			$this->defaultFormat = $defaultFormat;
			$this->payloadClass = $payloadClass;
			$this->validator = $validator;

			if ($validator) {
				$this->validatorGroups = $validatorGroups ?: [
					DecoderIntent::CLONE => [
						Constraint::DEFAULT_GROUP,
						static::GROUP_CLONE,
					],
					DecoderIntent::CREATE => [
						Constraint::DEFAULT_GROUP,
						static::GROUP_CREATE,
					],
					DecoderIntent::UPDATE => [
						Constraint::DEFAULT_GROUP,
						static::GROUP_UPDATE,
					],
				];
			}
		}

		/**
		 * {@inheritdoc}
		 */
		public function parse(string $intent, string $input, ?string $format = null): object {
			$payload = $this->serializer->deserialize(
				$input,
				$this->getPayloadClass(),
				$format ?? $this->getDefaultFormat(),
				$this->getDeserializeContext()
			);

			if ($this->validator) {
				$groups = $this->getGroups($intent);

				if ($groups === null)
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
		public function setDefaultFormat(string $defaultFormat): SymfonyDeserializeDecoder {
			$this->defaultFormat = $defaultFormat;

			return $this;
		}

		/**
		 * @return string
		 */
		public function getPayloadClass(): string {
			return $this->payloadClass;
		}

		/**
		 * @param string $payloadClass
		 *
		 * @return $this
		 */
		public function setPayloadClass(string $payloadClass): SymfonyDeserializeDecoder {
			$this->payloadClass = $payloadClass;

			return $this;
		}

		/**
		 * Returns the validator groups to use for the given {@see DecoderIntent}, or `null` if there are no groups
		 * defined.
		 *
		 * @param string $intent
		 *
		 * @return string[]|null
		 * @see DecoderIntent
		 */
		public function getGroups(string $intent): ?array {
			return $this->validatorGroups[$intent] ?? null;
		}

		/**
		 * @param string $intent
		 * @param array  $groups
		 *
		 * @return $this
		 */
		public function setGroups(string $intent, array $groups): SymfonyDeserializeDecoder {
			$this->validatorGroups[$intent] = $groups;

			return $this;
		}

		/**
		 * @return string[]
		 * @deprecated deprecated since version 1.5.1
		 *
		 */
		public function getCreateGroups(): array {
			return $this->getGroups(DecoderIntent::CREATE);
		}

		/**
		 * @param string[] $createGroups
		 *
		 * @return $this
		 * @deprecated deprecated since version 1.5.1
		 *
		 */
		public function setCreateGroups(array $createGroups): SymfonyDeserializeDecoder {
			$this->setGroups(DecoderIntent::CREATE, $createGroups);

			return $this;
		}

		/**
		 * @return string[]
		 * @deprecated deprecated since version 1.5.1
		 *
		 */
		public function getUpdateGroups(): array {
			return $this->getGroups(DecoderIntent::UPDATE);
		}

		/**
		 * @param string[] $updateGroups
		 *
		 * @return $this
		 * @deprecated deprecated since version 1.5.1
		 *
		 */
		public function setUpdateGroups(array $updateGroups): SymfonyDeserializeDecoder {
			$this->setGroups(DecoderIntent::UPDATE, $updateGroups);

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
		public function setDeserializeContext(array $deserializeContext): SymfonyDeserializeDecoder {
			$this->deserializeContext = $deserializeContext;

			return $this;
		}
	}