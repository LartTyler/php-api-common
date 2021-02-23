<?php
	namespace Payload\Registry\Factories;

	use DaybreakStudios\RestApiCommon\Payload\Decoders\SymfonyDeserializeDecoder;
	use DaybreakStudios\RestApiCommon\Payload\Registry\Factories\SymfonyDeserializeDecoderFactory;
	use PHPUnit\Framework\TestCase;
	use Symfony\Component\Serializer\Serializer;

	class SymfonyDeserializeDecoderFactoryTest extends TestCase {
		public function testCreatesInstances() {
			$factory = new SymfonyDeserializeDecoderFactory($this->createMock(Serializer::class), 'json');

			$this->assertInstanceOf(SymfonyDeserializeDecoder::class, $factory->create(TestDTO::class));
			$this->assertInstanceOf(SymfonyDeserializeDecoder::class, $factory->create(OtherDTO::class));
		}
	}

	class TestDTO {
		/**
		 * @var string
		 */
		public $name;
	}

	class OtherDTO {
		/**
		 * @var bool
		 */
		public $admin = false;
	}