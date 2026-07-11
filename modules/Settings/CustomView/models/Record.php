<?php
class Settings_CustomView_Record_Model extends Settings_Vtiger_Record_Model {
	public function getId() { return $this->get("cvid"); }
	public function getName() { return $this->get("viewname"); }
}
