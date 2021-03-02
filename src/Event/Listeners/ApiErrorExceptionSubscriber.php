<?php
	namespace DaybreakStudios\RestApiCommon\Event\Listeners;

	use DaybreakStudios\RestApiCommon\Exceptions\ApiErrorException;
	use DaybreakStudios\RestApiCommon\ResponderService;
	use Symfony\Component\EventDispatcher\EventSubscriberInterface;
	use Symfony\Component\HttpKernel\Event\ExceptionEvent;

	class ApiErrorExceptionSubscriber implements EventSubscriberInterface {
		/**
		 * @var ResponderService
		 */
		protected ResponderService $responder;

		/**
		 * ApiErrorExceptionSubscriber constructor.
		 *
		 * @param ResponderService $responder
		 */
		public function __construct(ResponderService $responder) {
			$this->responder = $responder;
		}

		/**
		 * @param ExceptionEvent $event
		 */
		public function onKernelException(ExceptionEvent $event): void {
			$exception = $event->getThrowable();

			if ($exception instanceof ApiErrorException)
				$event->setResponse($this->responder->createErrorResponse($exception->getApiError()));
		}

		/**
		 * {@inheritdoc}
		 */
		public static function getSubscribedEvents(): array {
			return [
				'kernel.exception' => 'onKernelException',
			];
		}
	}