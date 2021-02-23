<?php
	namespace Payload\Decoders;

	use DaybreakStudios\RestApiCommon\Error\Errors\Validation\ValidationFailedError;
	use DaybreakStudios\RestApiCommon\Exceptions\ApiErrorException;
	use DaybreakStudios\RestApiCommon\Payload\DecoderIntent;
	use DaybreakStudios\RestApiCommon\Payload\Decoders\SymfonyDeserializeDecoder;
	use Doctrine\Common\Annotations\AnnotationRegistry;
	use PHPUnit\Framework\TestCase;
	use Symfony\Component\Serializer\Encoder\JsonEncoder;
	use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
	use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
	use Symfony\Component\Serializer\Serializer;
	use Symfony\Component\Validator\Constraints as Assert;
	use Symfony\Component\Validator\Validation;

	class SymfonyDeserializeDecoderTest extends TestCase {
		/**
		 * @var SymfonyDeserializeDecoder
		 */
		protected $decoder;

		public function testBasicDecode() {
			/** @var TestPayloadDTO $payload */
			$payload = $this->decoder->parse(
				DecoderIntent::CREATE,
				json_encode(
					[
						'name' => 'Tyler',
						'admin' => false,
					]
				)
			);

			$this->assertEquals('Tyler', $payload->name);
			$this->assertFalse($payload->admin);

			$payload = $this->decoder->parse(
				DecoderIntent::UPDATE,
				json_encode(
					[
						'name' => 'Tyler',
						'admin' => false,
					]
				)
			);

			$this->assertEquals('Tyler', $payload->name);
			$this->assertFalse($payload->admin);
		}

		public function testCreateValidation() {
			$this->expectException(ApiErrorException::class);

			try {
				$payload = $this->decoder->parse(DecoderIntent::CREATE, '{}');
				var_dump($payload);
			} catch (ApiErrorException $exception) {
				$this->assertInstanceOf(ValidationFailedError::class, $exception->getApiError());

				throw $exception;
			}

			$this->expectException(ApiErrorException::class);

			try {
				$this->decoder->parse(DecoderIntent::CREATE, '{"admin":"Tyler"}');
			} catch (ApiErrorException $exception) {
				$this->assertInstanceOf(ValidationFailedError::class, $exception->getApiError());

				throw $exception;
			}

			/** @var TestPayloadDTO $payload */
			$payload = $this->decoder->parse(
				DecoderIntent::CREATE,
				json_encode(
					[
						'name' => 'Tyler',
					]
				)
			);

			$this->assertEquals('Tyler', $payload->name);
			$this->assertFalse($payload->admin);
		}

		public function testUpdateValidation() {
			/** @var TestPayloadDTO $payload */
			$payload = $this->decoder->parse(
				DecoderIntent::UPDATE,
				json_encode(
					[
						'admin' => true,
					]
				)
			);

			$this->assertNull($payload->name);
			$this->assertTrue($payload->admin);
		}

		/**
		 * @return void
		 */
		protected function setUp(): void {
			$serializer = new Serializer(
				[
					new DateTimeNormalizer(),
					new ObjectNormalizer(),
				], [
					new JsonEncoder(),
				]
			);

			AnnotationRegistry::registerLoader([require __DIR__ . '/../../../vendor/autoload.php', 'loadClass']);

			$this->decoder = new SymfonyDeserializeDecoder(
				$serializer,
				'json',
				TestPayloadDTO::class,
				Validation::createValidatorBuilder()
					->enableAnnotationMapping()
					->getValidator()
			);
		}
	}

	class TestPayloadDTO {
		/**
		 * @Assert\Type("string")
		 * @Assert\Length(min="1")
		 * @Assert\NotNull(groups={"create"})
		 *
		 * @var string
		 */
		public $name;

		/**
		 * @Assert\Type("bool")
		 *
		 * @var bool
		 */
		public $admin = false;
	}
