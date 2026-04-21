<?php
class CardLink {
    protected PDO $db;
    public function __construct() {
        $this->db = DB::conn();
    }
}
