<?php
	namespace DaybreakStudios\RestApiCommon\Event\Listeners;

	use DaybreakStudios\RestApiCommon\Error\Errors\AccessDeniedError;
	use DaybreakStudios\RestApiCommon\ResponderService;
	use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
	use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent;
	use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
	use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
	use Lexik\Bundle\JWTAuthenticationBundle\Events;
	use Symfony\Component\EventDispatcher\EventSubscriberInterface;

	/**
	 * Used to transform generic error messages from the `lexik/jwt-authentication-bundle` into error messages that are
	 * consumable by an end-user.
	 *
	 * @package DaybreakStudios\RestApiCommon\Event\Listeners
	 */
	class LexikJwtAuthenticationFailureSubscriber implements EventSubscriberInterface {
		/**
		 * @var ResponderService
		 */
		protected $responder;

		/**
		 * LexikJwtAuthenticationFailureListener constructor.
		 *
		 * @param ResponderService $responder
		 */
		public function __construct(ResponderService $responder) {
			$this->responder = $responder;
		}

		/**
		 * {@inheritdoc}
		 */
		public static function getSubscribedEvents() {
			return [
				Events::AUTHENTICATION_FAILURE => 'onAuthenticationFailure',
				Events::JWT_INVALID => 'onInvalidToken',
				Events::JWT_NOT_FOUND => 'onTokenNotFound',
				Events::JWT_EXPIRED => 'onTokenExpired',
			];
		}

		/**
		 * @param AuthenticationFailureEvent $event
		 *
		 * @return void
		 */
		public function onAuthenticationFailure(AuthenticationFailureEvent $event): void {
			$error = new AccessDeniedError('Credentials not found, please verify your username and password');

			$event->setResponse($this->responder->createErrorResponse($error));
		}

		/**
		 * @param JWTInvalidEvent $event
		 *
		 * @return void
		 */
		public function onInvalidToken(JWTInvalidEvent $event): void {
			$error = new AccessDeniedError('Your token is invalid, please log in again to get a new one');

			$event->setResponse($this->responder->createErrorResponse($error));
		}

		/**
		 * @param JWTNotFoundEvent $event
		 *
		 * @return void
		 */
		public function onTokenNotFound(JWTNotFoundEvent $event): void {
			$error = new AccessDeniedError('You must pass a token in the Authorization header to access this resource');

			$event->setResponse($this->responder->createErrorResponse($error));
		}

		/**
		 * @param JWTExpiredEvent $event
		 *
		 * @return void
		 */
		public function onTokenExpired(JWTExpiredEvent $event): void {
			$error = new AccessDeniedError('Your token is expired, please log in again to get a new one');

			$event->setResponse($this->responder->createErrorResponse($error));
		}
	}