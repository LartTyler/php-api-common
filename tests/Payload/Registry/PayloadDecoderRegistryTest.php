<?php
	namespace Payload\Registry;

	use DaybreakStudios\RestApiCommon\Payload\PayloadDecoderInterface;
	use DaybreakStudios\RestApiCommon\Payload\Registry\Factories\SymfonyDeserializeDecoderFactory;
	use DaybreakStudios\RestApiCommon\Payload\Registry\PayloadDecoderFactoryInterface;
	use DaybreakStudios\RestApiCommon\Payload\Registry\PayloadDecoderRegistry;
	use PHPUnit\Framework\TestCase;

	class PayloadDecoderRegistryTest extends TestCase {
		/**
		 * @var PayloadDecoderRegistry
		 */
		protected $registry;

		/**
		 * {@inheritdoc}
		 */
		public function setUp(): void {
			$decoder = $this->createMock(PayloadDecoderInterface::class);

			$factory = $this->createMock(PayloadDecoderFactoryInterface::class);
			$factory
				->method('create')
				->with(TestDTO::class)
				->willReturn($decoder);

			$this->registry = new PayloadDecoderRegistry($factory);
		}

		/**
		 * @return void
		 */
		public function testReturnsDecoder(): void {
			$this->assertInstanceOf(PayloadDecoderInterface::class, $this->registry->getDecoder(TestDTO::class));
		}

		/**
		 * @return void
		 */
		public function testReusesDecoder(): void {
			$decoder = $this->registry->getDecoder(TestDTO::class);

			$this->assertEquals($decoder, $this->registry->getDecoder(TestDTO::class));
		}
	}

	class TestDTO {
		/**
		 * @var string
		 */
		public $name;
	}