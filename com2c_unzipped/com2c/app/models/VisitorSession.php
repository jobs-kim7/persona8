<?php
class VisitorSession {
    protected PDO $db;
    public function __construct() {
        $this->db = DB::conn();
    }
}
