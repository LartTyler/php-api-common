<?php
	namespace DaybreakStudios\RestApiCommon\Payload;

	use DaybreakStudios\RestApiCommon\Utility\ConstantsClassTrait;

	final class DecoderIntent {
		use ConstantsClassTrait;

		public const CREATE = 'create';
		public const UPDATE = 'update';
	}