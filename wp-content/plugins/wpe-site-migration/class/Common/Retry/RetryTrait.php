<?php

namespace DeliciousBrains\WPMDB\Common\Retry;

use DeliciousBrains\WPMDB\Common\Exceptions\UnknownStateProperty;
use DeliciousBrains\WPMDB\Common\MigrationPersistence\Persistence;
use DeliciousBrains\WPMDB\Common\MigrationState\Migrations\CurrentMigrationState;
use DeliciousBrains\WPMDB\Common\MigrationState\StateFactory;
use WP_Error;

trait RetryTrait {
	/**
	 * Handles the returned errors and updates the current_migration state with
	 * the error count for the current stage.
	 *
	 * @param WP_Error $error
	 * @param string   $stage
	 *
	 * @return bool whether the error qualifies for retry.
	 */
	abstract protected function handle_error( $error, $stage );

	/**
	 * Get the error count from the current_migration state.
	 *
	 * @param CurrentMigrationState $current_migration Optional.
	 *
	 * @return int
	 */
	protected static function get_error_count( CurrentMigrationState $current_migration = null ) {
		if ( empty( $current_migration ) ) {
			$current_migration = StateFactory::create( 'current_migration' )->load_state( null );
		}

		if ( is_a( $current_migration, CurrentMigrationState::class ) ) {
			try {
				return (int) $current_migration->get( self::RETRY_ERROR_COUNT_PROPERTY );
			} catch ( UnknownStateProperty $exception ) {
				return 0;
			}
		}

		return 0;
	}

	/**
	 * Increment the error count in the current_migration state.
	 *
	 * @return void
	 */
	protected static function increment_error_count() {
		$current_migration = StateFactory::create( 'current_migration' )->load_state( null );

		if ( is_a( $current_migration, CurrentMigrationState::class ) ) {
			$count = self::get_error_count( $current_migration );
			$current_migration->set( self::RETRY_ERROR_COUNT_PROPERTY, $count + 1, false );
			$current_migration->update_state();
		}

		// As error count is reset on success, we also want to keep track of
		// total errors that could be retried.
		Persistence::incrementMigrationStat( self::RETRY_ERROR_COUNT_TOTAL_PROPERTY );
	}

	/**
	 * Resets the error count in the current_migration state.
	 *
	 * @return void
	 */
	protected static function reset_error_count() {
		$current_migration = StateFactory::create( 'current_migration' )->load_state( null );

		if ( is_a( $current_migration, CurrentMigrationState::class ) ) {
			$current_migration->set( self::RETRY_ERROR_COUNT_PROPERTY, 0, false );
			$current_migration->update_state();
		}
	}

	/**
	 * Updates the migration stats with the errors that occurred during the stage.
	 *
	 * @param string $stage  The stage of the migration.
	 * @param array  $errors The errors that occurred during the stage.
	 *
	 * @return bool
	 */
	protected static function update_error_stats( $stage, $errors ) {
		if ( ! is_array( $errors ) ) {
			return false;
		}

		foreach ( $errors as $key => $error ) {
			$stat_error = Persistence::getMigrationErrorFromStats( $stage, $key );

			if ( ! is_array( $stat_error ) || empty( $stat_error['error_count'] ) || ! is_numeric( $stat_error['error_count'] ) ) {
				$stat_error                = [];
				$stat_error['error_count'] = 1;
			} else {
				$stat_error['error_count'] += 1;
			}

			if ( empty( $stat_error['error_timestamps'] ) || ! is_array( $stat_error['error_timestamps'] ) ) {
				$stat_error['error_timestamps'] = [];
			}

			$stat_error['error_timestamps'][] = time();

			Persistence::addMigrationErrorToStats( $stage, $key, $stat_error );
		}

		return true;
	}

	/**
	 * Should we retry the failed item, or are we done with retrying?
	 *
	 * @param WP_Error $error
	 * @param string   $stage
	 *
	 * @return bool
	 */
	protected static function should_retry( $error, $stage ) {
		$error_count = self::get_error_count();

		// Save the last retry count property's value into migrations stats.
		// This will survive count reset.
		Persistence::setMigrationStat( self::RETRY_ERROR_COUNT_PROPERTY, $error_count );

		// Keep track of maximum number of retries that were reached.
		$max_error_count = Persistence::getMigrationStat( self::RETRY_ERROR_COUNT_PROPERTY . '_max' );

		if ( $error_count > $max_error_count ) {
			Persistence::setMigrationStat( self::RETRY_ERROR_COUNT_PROPERTY . '_max', $error_count );
		}

		if ( $error_count >= self::RETRY_COUNT_LIMIT ) {
			return false;
		}

		return true;
	}
}
