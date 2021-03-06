<?php

class Cockpit_Campus_Cash_Log extends Cana_Table {

	const ACTION_RETRIEVED = 'retrieved';
	const ACTION_DELETED = 'deleted';

	const VIEW_LIMIT = 3;

	public function __construct($id = null) {
		parent::__construct();
		$this
			->table('campus_cash_log')
			->idVar('id_campus_cash_log')
			->load($id);
	}

	public static function timesViewed( $id_user_payment_type ){
		$query = 'SELECT COUNT(*) AS total FROM campus_cash_log WHERE id_user_payment_type = ? ';
		$result = c::db()->get( $query, [ $id_user_payment_type ] );
		return intval( $result->get( 0 )->total );
	}

	public static function retrieved( $id_user_payment_type ){
		$log = new Cockpit_Campus_Cash_Log;
		$log->id_admin = c::user()->id_admin;
		$log->id_user_payment_type = $id_user_payment_type;
		$log->action = self::ACTION_RETRIEVED;
		$log->datetime = date( 'Y-m-d H:i:s' );
		$log->save();
	}

	public static function deleted( $id_user_payment_type ){
		$log = new Cockpit_Campus_Cash_Log;
		$log->id_admin = c::user()->id_admin;
		$log->id_user_payment_type = $id_user_payment_type;
		$log->action = self::ACTION_DELETED;
		$log->datetime = date( 'Y-m-d H:i:s' );
		$log->save();
	}
}