<?php

namespace WPConnectr;

defined('ABSPATH') || exit;

class Logger {
	/**
	 * Default log level. Everything which equal or below is always logged
	 * regardless of whether detailed logging is enabled in settings.
	 */
	const DEFAULT_LEVEL = 4;

	/**
	 * Valid log levels
	 *
	 * @var array
	 */
	protected $levels = array(
		5 => array( 'emergency' ),
		4 => array( 'alert', 'critical', 'error' ),
		3 => array( 'warning' ),
		2 => array( 'info', 'notice' ),
		1 => array( 'debug' ),
	);

	/**
	 * Logger instance.
	 *
	 * @var GFLogging
	 */
	protected $logger;

	/**
	 * Instance of the logger.
	 *
	 * @var Logger
	 */
	protected static $instance;

	public static function get_instance() {
		if ( ! isset( static::$instance ) ) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	public static function log_message( $message, $log_level = 'info' ) {
		$logger = static::get_instance();
		return $logger->log( $log_level, $message );
	}

	/**
	 * Logger context used to group content together.
	 *
	 * @var array
	 */
	protected $context;

	public function __construct() {
		$this->logger = new class() {
			/**
			 * Valid log levels
			 *
			 * @var array
			 */
			protected $levels = array(
				5 => array( 'emergency' ),
				4 => array( 'alert', 'critical', 'error' ),
				3 => array( 'warning' ),
				2 => array( 'info', 'notice' ),
				1 => array( 'debug' ),
			);

			public function log_message( $slug, $message, $log_level = 'info' ) {
				if ( is_numeric( $log_level ) ) {
					$log_level = $this->levels[ $log_level ][0];
				}

				$log_level = strtoupper( $log_level );

				error_log( "[{$log_level}]: {$message}" );
			}
		};
	}

	/**
	 * System is unusable.
	 *
	 * @param  string       $message The message to be logged. Can be formatted for printf.
	 * @param  array|string $context [optional] Dynamic part of the formatted message.
	 *
	 * @return void
	 */
	public function emergency( $message, $context = array() ) {
		$this->log( 'emergency', $message, $context );
	}

	/**
	 * Action must be taken immediately
	 *
	 * @param  string       $message The message to be logged. Can be formatted for printf.
	 * @param  array|string $context [optional] Dynamic part of the formatted message.
	 *
	 * @return void
	 */
	public function alert( $message, $context = array() ) {
		$this->log( 'alert', $message, $context );
	}

	/**
	 * Critical conditions
	 *
	 * @param  string       $message The message to be logged. Can be formatted for printf.
	 * @param  array|string $context [optional] Dynamic part of the formatted message.
	 *
	 * @return void
	 */
	public function critical( $message, $context = array() ) {
		$this->log( 'critical', $message, $context );
	}

	/**
	 * Runtime errors that do not require immediate action but should typically
	 * be logged and monitored
	 *
	 * @param  string       $message The message to be logged. Can be formatted for printf.
	 * @param  array|string $context [optional] Dynamic part of the formatted message.
	 *
	 * @return void
	 */
	public function error( $message, $context = array() ) {
		$this->log( 'error', $message, $context );
	}

	/**
	 * Exceptional occurrences that are not errors
	 *
	 * @param  string       $message The message to be logged. Can be formatted for printf.
	 * @param  array|string $context [optional] Dynamic part of the formatted message.
	 *
	 * @return void
	 */
	public function warning( $message, $context = array() ) {
		$this->log( 'warning', $message, $context );
	}

	/**
	 * Normal but significant events
	 *
	 * @param  string       $message The message to be logged. Can be formatted for printf.
	 * @param  array|string $context [optional] Dynamic part of the formatted message.
	 *
	 * @return void
	 */
	public function notice( $message, $context = array() ) {
		$this->log( 'notice', $message, $context );
	}

	/**
	 * Interesting events
	 *
	 * @param  string       $message The message to be logged. Can be formatted for printf.
	 * @param  array|string $context [optional] Dynamic part of the formatted message.
	 *
	 * @return void
	 */
	public function info( $message, $context = array() ) {
		$this->log( 'info', $message, $context );
	}

	/**
	 * Detailed debug information
	 *
	 * @param  string       $message The message to be logged. Can be formatted for printf.
	 * @param  array|string $context [optional] Dynamic part of the formatted message.
	 *
	 * @return void
	 */
	public function debug( $message, $context = array() ) {
		$this->log( 'debug', $message, $context );
	}

	/**
	 * Logs with an arbitrary level
	 *
	 * @param  string       $log_level  The name of the logging level.
	 * @param  string       $message    The message to be logged. Can be formatted for printf.
	 * @param  array|string $context    [optional] Dynamic part of the formatted message.
	 *
	 * @throws InvalidLogLevelException In case the log level is invalid.
	 *
	 * @return void
	 */
	public function log( $log_level, $message, $context = array() ) {
		$message      = $this->assemble_message( $message, $context );
		$message_type = static::DEFAULT_LEVEL;

		foreach ($this->levels as $level => $labels) {
			if (in_array($log_level, $labels, true)) {
				$message_type = $level;
			}
		}

		$this->logger->log_message( WP_CONNECTR_SLUG, $message, $message_type );
	}

	/**
	 * Combine message with provided context
	 * Using vsprintf for formatting.
	 *
	 * @param  string       $message The message to be logged. Can be formatted for printf.
	 * @param  array|string $context [optional] Dynamic part of the formatted message.
	 *
	 * @return string
	 */
	protected function assemble_message( $message, $context = array() ) {
		$context = is_array( $context ) ? $context : array( $context );
		if ( ! empty( $context ) ) {
			return vsprintf( $message, $context );
		}
		return $message;
	}

}
