<?php
class CardPortfolioItem {
    protected PDO $db;
    public function __construct() {
        $this->db = DB::conn();
    }
}
