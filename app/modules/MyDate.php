<?php

	class Module_MyDate {

		public $dt;

		public function __construct($db, $tz) {
			$tz = new DateTimeZone($tz);
			$this->dt = new DateTime();
			$this->dt->setTimezone($tz);
		}

		public function fromInt($f, $t = null) {
			if(is_null($t)) $t = time();
			$this->dt->setTimestamp($t);
			return $this->dt->format($f);
		}

		public function fromStr($f, $s) {
			$t = strtotime($s);
			if(!$t) return false;
			return $this->fromInt($f, $t);
		}

	}
