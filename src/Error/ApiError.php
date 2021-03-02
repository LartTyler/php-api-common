<?php
	namespace DaybreakStudios\RestApiCommon\Error;

	class ApiError implements ApiErrorInterface {
		/**
		 * @var string
		 */
		protected $code;

		/**
		 * @var string
		 */
		protected string $message;

		/**
		 * @var int|null
		 */
		protected ?int $httpStatus;

		/**
		 * @var array|null
		 */
		protected ?array $context = null;

		/**
		 * ApiError constructor.
		 *
		 * @param string   $code
		 * @param string   $message
		 * @param int|null $httpStatus
		 */
		public function __construct(string $code, string $message, ?int $httpStatus = null) {
			$this->code = $code;
			$this->message = $message;
			$this->httpStatus = $httpStatus;
		}

		/**
		 * @return string
		 */
		public function getCode(): string {
			return $this->code;
		}

		/**
		 * @param string $code
		 */
		public function setCode(string $code): void {
			$this->code = $code;
		}

		/**
		 * @return string
		 */
		public function getMessage(): string {
			return $this->message;
		}

		/**
		 * @param string $message
		 */
		public function setMessage(string $message): void {
			$this->message = $message;
		}

		/**
		 * @return int|null
		 */
		public function getHttpStatus(): ?int {
			return $this->httpStatus;
		}

		/**
		 * @param int|null $httpStatus
		 */
		public function setHttpStatus(?int $httpStatus): void {
			$this->httpStatus = $httpStatus;
		}

		/**
		 * @return array|null
		 */
		public function getContext(): ?array {
			return $this->context;
		}

		/**
		 * @param array|null $context
		 */
		public function setContext(?array $context): void {
			$this->context = $context;
		}
	}