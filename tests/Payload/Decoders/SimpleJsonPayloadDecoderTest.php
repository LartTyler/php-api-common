<?php
	namespace Payload\Decoders;

	use DaybreakStudios\RestApiCommon\Payload\DecoderIntent;
	use DaybreakStudios\RestApiCommon\Payload\Decoders\SimpleJsonPayloadDecoder;
	use PHPUnit\Framework\TestCase;

	class SimpleJsonPayloadDecoderTest extends TestCase {
		/**
		 * @return void
		 */
		public function testBasicDecode(): void {
			$decoder = new SimpleJsonPayloadDecoder();

			$this->assertEquals(
				(object)['test' => true],
				$decoder->parse(DecoderIntent::CREATE, '{"test":true}')
			);
		}
	}