<?php
class CreditController extends Controller {
    public function index(): void {
        $this->view('credits/index', [
            'selfCredit' => 2300,
            'partnerCredit' => 300,
        ]);
    }
}
