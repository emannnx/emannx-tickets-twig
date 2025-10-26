<?php
namespace App\Controllers;

use App\Utils\Auth;
use App\Models\Ticket;

class DashboardController {
    private $twig;

    public function __construct($twig) {
        $this->twig = $twig;
    }

    public function index() {
        Auth::requireAuth();

        $allTickets = Ticket::getAll();
        $stats = $this->calculateStats($allTickets);
        $recentTickets = $this->getRecentTickets($allTickets);
        $urgentTickets = $this->getUrgentTickets($allTickets);
        
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        echo $this->twig->render('pages/dashboard.html.twig', [
            'stats' => $stats,
            'recentTickets' => $recentTickets,
            'urgentTickets' => $urgentTickets,
            'avgResponseTime' => $this->calculateAvgResponseTime($allTickets),
            'flash' => $flash
        ]);
    }

    private function calculateStats($tickets) {
        $total = count($tickets);
        $open = array_filter($tickets, function($t) { return $t['status'] === 'open'; });
        $inProgress = array_filter($tickets, function($t) { return $t['status'] === 'in_progress'; });
        $resolved = array_filter($tickets, function($t) { return $t['status'] === 'closed'; });

        return [
            'total' => $total,
            'open' => count($open),
            'in_progress' => count($inProgress),
            'resolved' => count($resolved)
        ];
    }

    private function getRecentTickets($tickets) {
        // Sort by creation date, newest first
        usort($tickets, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return array_slice($tickets, 0, 6); // Return 6 most recent
    }

    private function getUrgentTickets($tickets) {
        return array_filter($tickets, function($ticket) {
            return ($ticket['priority'] === 'Urgent' || $ticket['priority'] === 'High') && $ticket['status'] !== 'closed';
        });
    }

    private function calculateAvgResponseTime($tickets) {
        // Simple mock calculation - in real app, you'd calculate actual response times
        $closedTickets = array_filter($tickets, function($t) { return $t['status'] === 'closed'; });
        
        if (count($closedTickets) === 0) {
            return '24';
        }
        
        // Mock average response time in hours
        return rand(4, 48);
    }
}