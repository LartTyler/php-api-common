<?php
	namespace DaybreakStudios\RestApiCommon\Error;

	interface ApiErrorInterface {
		/**
		 * @return string
		 */
		public function getCode(): string;

		/**
		 * @return string
		 */
		public function getMessage(): string;

		/**
		 * @return int|null
		 */
		public function getHttpStatus(): ?int;

		/**
		 * @return array|null
		 */
		public function getContext(): ?array;
	}