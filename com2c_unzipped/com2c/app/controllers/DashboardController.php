<?php
class DashboardController extends Controller
{
    public function index(): void
    {
        $db = DB::conn();

        // MVP 임시: 로그인 전이므로 user_id 고정
        $userId = 1;

        $stmt = $db->prepare("
            SELECT *
            FROM cards
            WHERE user_id = :user_id
            ORDER BY updated_at DESC, id DESC
        ");
        $stmt->execute([':user_id' => $userId]);
        $cards = $stmt->fetchAll();

        $recentCard = $cards[0] ?? null;

        $this->view('dashboard/index', [
            'recentCard' => $recentCard,
            'cards' => $cards,
            'credit' => 2300,
        ], 'dashboard');
    }
}