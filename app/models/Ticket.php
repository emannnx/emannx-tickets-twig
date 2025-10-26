<?php
namespace App\Models;

class Ticket {
    private static $file = __DIR__ . '/../../data/tickets.json';

    public static function getAll() {
        if (!file_exists(self::$file)) {
            return [];
        }
        $data = file_get_contents(self::$file);
        return json_decode($data, true) ?: [];
    }

    public static function saveAll($tickets) {
        $dir = dirname(self::$file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents(self::$file, json_encode($tickets, JSON_PRETTY_PRINT));
    }

    public static function create($data) {
        $tickets = self::getAll();
        $ticket = [
            'id' => uniqid(),
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'status' => $data['status'],
            'priority' => $data['priority'] ?? 'Normal',
            'created_by' => \App\Utils\Session::get('user_email'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        $tickets[] = $ticket;
        self::saveAll($tickets);
        return $ticket;
    }

    public static function update($id, $data) {
        $tickets = self::getAll();
        $found = false;
        
        foreach ($tickets as &$ticket) {
            if ($ticket['id'] === $id) {
                $ticket['title'] = $data['title'];
                $ticket['description'] = $data['description'] ?? '';
                $ticket['status'] = $data['status'];
                $ticket['priority'] = $data['priority'] ?? 'Normal';
                $ticket['updated_at'] = date('Y-m-d H:i:s');
                $found = true;
                break;
            }
        }
        
        if ($found) {
            self::saveAll($tickets);
            return true;
        }
        return false;
    }

    public static function delete($id) {
        $tickets = self::getAll();
        $tickets = array_filter($tickets, function($ticket) use ($id) {
            return $ticket['id'] !== $id;
        });
        self::saveAll(array_values($tickets));
        return true;
    }

    public static function find($id) {
        $tickets = self::getAll();
        foreach ($tickets as $ticket) {
            if ($ticket['id'] === $id) {
                return $ticket;
            }
        }
        return null;
    }

    public static function getStats() {
        $tickets = self::getAll();
        $total = count($tickets);
        $open = array_filter($tickets, function($t) { return $t['status'] === 'open'; });
        $resolved = array_filter($tickets, function($t) { return $t['status'] === 'closed'; });

        return [
            'total' => $total,
            'open' => count($open),
            'resolved' => count($resolved)
        ];
    }
}