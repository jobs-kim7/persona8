<?php
class CardSponsorSetting {
    protected PDO $db;
    public function __construct() {
        $this->db = DB::conn();
    }
}
