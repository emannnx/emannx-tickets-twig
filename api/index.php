<?php
// Vercel deployment settings
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/config/config.php';

use App\Controllers\PagesController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\TicketController;
use App\Models\Ticket;

// Session handling for serverless
if (isset($_COOKIE['PHPSESSID'])) {
    session_id($_COOKIE['PHPSESSID']);
}
session_start();

// Setup Twig
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader, [
    'debug' => false,
    'cache' => false,
]);

// Add global variables to Twig
$twig->addGlobal('app', [
    'request' => [
        'pathinfo' => $_SERVER['PATH_INFO'] ?? '/'
    ],
    'session' => $_SESSION
]);

// Initialize controllers
$pagesController = new PagesController($twig);
$authController = new AuthController($twig);
$dashboardController = new DashboardController($twig);
$ticketController = new TicketController($twig);

// Simple routing
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Route the request
switch ($path) {
    case '/':
        $pagesController->landing();
        break;
        
    case '/auth/login':
        $authController->login();
        break;
        
    case '/auth/signup':
        $authController->signup();
        break;
        
    case '/auth/logout':
        $authController->logout();
        break;
        
    case '/dashboard':
        $dashboardController->index();
        break;
        
    case '/tickets':
        $ticketController->index();
        break;
        
    case '/tickets/create':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ticketController->create();
        } else {
            // Show empty form for GET request
            $tickets = Ticket::getAll();
            echo $twig->render('pages/tickets.html.twig', [
                'showForm' => true,
                'tickets' => $tickets,
                'editingId' => null,
                'errors' => [],
                'old' => []
            ]);
        }
        break;
        
    default:
        // Handle dynamic routes
        if (preg_match('#^/tickets/edit/([^/]+)$#', $path, $matches)) {
            $id = $matches[1];
            $ticketController->edit($id);
        } elseif (preg_match('#^/tickets/update/([^/]+)$#', $path, $matches)) {
            $id = $matches[1];
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $ticketController->update($id);
            } else {
                header('Location: /tickets');
            }
        } elseif (preg_match('#^/tickets/delete/([^/]+)$#', $path, $matches)) {
            $id = $matches[1];
            $ticketController->delete($id);
        } else {
            // 404
            http_response_code(404);
            echo $twig->render('pages/404.html.twig');
        }
        break;
}