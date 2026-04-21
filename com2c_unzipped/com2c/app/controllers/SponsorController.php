<?php
class SponsorController extends Controller {
    public function index(): void {
        $this->view('sponsors/index', [
            'mode' => 'self',
            'partner' => null,
        ]);
    }
}
