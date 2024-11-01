<?php

namespace WPConnectr\Trigger;

defined('ABSPATH') || exit;

/**
 * Definition of the Webhook resource type.
 * We call them Triggers to be consistent with Power Automate.
 *
 * @since 1.0.0
 */
class Trigger {
	/**
	 * Stores Trigger ID
	 *
	 * @var int
	 */
	protected $id = 0;

	/**
	 * Stores Trigger data
	 *
	 * @var array
	 */
	protected $data = array(
		'name'              => '',
		'status'            => 'disabled',
		'topic'             => '',
		'url'               => '',
		'failure_count'     => 0,
		'trigger_count'     => 0,
		'last_triggered_at' => null,
		'user_id'           => 0,
		'pending'           => false,
	);

	/**
	 * Core data changes for this object.
	 *
	 * @var array
	 */
	protected $changes = array();

	/**
	 * This is false until the object is read from the DB.
	 *
	 * @var bool
	 */
	protected $object_read = false;

	/**
	 * The DataStore used to manage this object.
	 *
	 * @var WPConnectr\Webhook\DataStore
	 */
	protected $data_store;


	/**
	 * Constructor
	 *
	 * @param int $id
	 */
	public function __construct( $id = 0 ) {
		$this->data_store = DataStore::get_instance();

		if ( $id > 0 ) {
			$this->id = $id;
			$this->data_store->read( $this );
		}
	}

	/**
	 * Get the ID of the trigger.
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Set ID.
	 *
	 * @param int $id ID.
	 */
	public function set_id( $id ) {
		$this->id = absint( $id );
	}

	/**
	 * Get the name of the trigger.
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->get_prop( 'name' );
	}

	public function get_status() {
		return $this->get_prop( 'status' );
	}

	public function get_topic() {
		return $this->get_prop( 'topic' );
	}

	public function get_url() {
		return $this->get_prop( 'url' );
	}

	public function get_failure_count() {
		return $this->get_prop( 'failure_count' );
	}

	public function get_user_id() {
		return $this->get_prop( 'user_id' );
	}

	public function get_last_triggered_at() {
		return $this->get_prop( 'last_triggered_at' );
	}

	public function set_object_read( $read ) {
		$this->object_read = (bool) $read;
	}

	/**
	 * Set a collection of props in one go, collect any errors, and return the result.
	 * Only sets using public methods.
	 *
	 * @param array  $props Key value pairs to set. Key is the prop and should map to a setter function name.
	 *
	 * @return bool
	 */
	public function set_props( $props ) {
		$errors = false;

		foreach ( $props as $prop => $value ) {
			/**
			 * Checks if the prop being set is allowed, and the value is not null.
			 */
			if ( is_null( $value ) ) {
				continue;
			}

			$this->set_prop( $prop, $value );
		}

		return $errors && count( $errors->get_error_codes() ) ? $errors : true;
	}

	/**
	 * Sets a prop for a setter method.
	 *
	 * This stores changes in a special array so we can track what needs saving
	 * the DB later.
	 *
	 * @param string $prop Name of prop to set.
	 * @param mixed  $value Value of the prop.
	 */
	protected function set_prop( $prop, $value ) {
		if ( array_key_exists( $prop, $this->data ) ) {
			if ( true === $this->object_read ) {
				if ( $value !== $this->data[ $prop ] || array_key_exists( $prop, $this->changes ) ) {
					$this->changes[ $prop ] = $value;
				}
			} else {
				$this->data[ $prop ] = $value;
			}
		}
	}

	/**
	 * Return data changes only.
	 *
	 * @return array
	 */
	public function get_changes() {
		return $this->changes;
	}

	/**
	 * Merge changes with data and clear.
	 *
	 */
	public function apply_changes() {
		$this->data    = array_replace_recursive( $this->data, $this->changes ); // @codingStandardsIgnoreLine
		$this->changes = array();
	}

	/**
	 * Gets a prop for a getter method.
	 *
	 * Gets the value from either current pending changes, or the data itself.
	 * Context controls what happens to the value before it's returned.
	 *
	 * @param  string $prop Name of prop to get.
	 * @return mixed
	 */
	protected function get_prop( $prop ) {
		$value = null;

		if ( array_key_exists( $prop, $this->data ) ) {
			$value = array_key_exists( $prop, $this->changes ) ? $this->changes[ $prop ] : $this->data[ $prop ];
		}

		return $value;
	}


}
