<?php
class User {
    protected PDO $db;
    public function __construct() {
        $this->db = DB::conn();
    }
}
