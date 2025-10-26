<?php
namespace App\Controllers;

use App\Utils\Auth;
use App\Models\Ticket;
use App\Utils\Validation;

class TicketController {
    private $twig;

    public function __construct($twig) {
        $this->twig = $twig;
    }

    public function index() {
        Auth::requireAuth();

        $tickets = Ticket::getAll();
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        echo $this->twig->render('pages/tickets.html.twig', [
            'tickets' => $tickets,
            'flash' => $flash,
            'showForm' => false
        ]);
    }

    public function create() {
        Auth::requireAuth();

        $errors = [];
        $old = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => $_POST['title'] ?? '',
                'description' => $_POST['description'] ?? '',
                'status' => $_POST['status'] ?? 'open',
                'priority' => $_POST['priority'] ?? 'Normal'
            ];

            $errors = Validation::validateTicket($data);
            $old = $data;

            if (empty($errors)) {
                Ticket::create($data);
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Ticket created successfully!'];
                header('Location: https://' . $_SERVER['HTTP_HOST'] . '/tickets');
                exit;
            }
        }

        // Show form with errors
        $tickets = Ticket::getAll();
        echo $this->twig->render('pages/tickets.html.twig', [
            'showForm' => true,
            'errors' => $errors,
            'old' => $old,
            'tickets' => $tickets,
            'editingId' => null
        ]);
    }

    public function edit($id) {
        Auth::requireAuth();

        $ticket = Ticket::find($id);
        
        if (!$ticket) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Ticket not found!'];
            header('Location: https://' . $_SERVER['HTTP_HOST'] . '/tickets');
            exit;
        }

        $tickets = Ticket::getAll();
        echo $this->twig->render('pages/tickets.html.twig', [
            'showForm' => true,
            'editingId' => $id,
            'ticket' => $ticket,
            'tickets' => $tickets,
            'errors' => [],
            'old' => []
        ]);
    }

    public function update($id) {
        Auth::requireAuth();

        $ticket = Ticket::find($id);
        if (!$ticket) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Ticket not found!'];
            header('Location: https://' . $_SERVER['HTTP_HOST'] . '/tickets');
            exit;
        }

        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => $_POST['title'] ?? '',
                'description' => $_POST['description'] ?? '',
                'status' => $_POST['status'] ?? 'open',
                'priority' => $_POST['priority'] ?? 'Normal'
            ];

            $errors = Validation::validateTicket($data);

            if (empty($errors)) {
                Ticket::update($id, $data);
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Ticket updated successfully!'];
                header('Location: https://' . $_SERVER['HTTP_HOST'] . '/tickets');
                exit;
            }
        }

        $tickets = Ticket::getAll();
        echo $this->twig->render('pages/tickets.html.twig', [
            'showForm' => true,
            'editingId' => $id,
            'ticket' => array_merge($ticket, $data ?? []),
            'errors' => $errors,
            'tickets' => $tickets,
            'old' => $data ?? []
        ]);
    }

    public function delete($id) {
        Auth::requireAuth();

        if (Ticket::find($id)) {
            Ticket::delete($id);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Ticket deleted successfully!'];
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Ticket not found!'];
        }
        
        header('Location: https://' . $_SERVER['HTTP_HOST'] . '/tickets');
        exit;
    }
}