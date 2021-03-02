<?php
	namespace DaybreakStudios\RestApiCommon\Payload\Decoders;

	use DaybreakStudios\RestApiCommon\Payload\Exceptions\PayloadDecoderException;
	use DaybreakStudios\RestApiCommon\Payload\PayloadDecoderInterface;

	class SimpleJsonPayloadDecoder implements PayloadDecoderInterface {
		/**
		 * @var int
		 */
		protected int $depth;

		/**
		 * @var int
		 */
		protected int $flags;

		/**
		 * SimpleJsonPayloadDecoder constructor.
		 *
		 * @param int $depth
		 * @param int $flags
		 */
		public function __construct(int $depth = 512, int $flags = 0) {
			$this->depth = $depth;
			$this->flags = $flags;
		}

		/**
		 * {@inheritdoc}
		 */
		public function parse(string $intent, string $input, ?string $format = null): object {
			if ($format && $format !== 'json')
				throw new \Exception(static::class . ' can only parse "json" formats');

			$payload = json_decode($input, false, $this->depth, $this->flags);

			if (json_last_error() !== JSON_ERROR_NONE)
				throw PayloadDecoderException::invalidJsonPayload(json_last_error_msg());

			return $payload;
		}
	}