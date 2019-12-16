<?php
	namespace DaybreakStudios\RestApiCommon\Utility;

	trait ConstantsClassTrait {
		/**
		 * @var string[]|null
		 */
		private static $values = null;

		/**
		 * ConstantsClassTrait constructor.
		 */
		private function __construct() {
		}

		/**
		 * @return string[]
		 */
		public static function values(): array {
			if (static::$values === null)
				static::$values = array_values((new \ReflectionClass(static::class))->getConstants());

			return static::$values;
		}
	}