<?php
namespace App\Models;

class Ticket {
    private static function getDB() {
        $dbPath = __DIR__ . '/../../data/tickets.db';
        $dbDir = dirname($dbPath);
        
        // Create data directory if it doesn't exist
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
        }
        
        try {
            $db = new \PDO("sqlite:$dbPath");
            $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            // Create table if it doesn't exist
            $db->exec("
                CREATE TABLE IF NOT EXISTS tickets (
                    id TEXT PRIMARY KEY,
                    title TEXT NOT NULL,
                    description TEXT,
                    status TEXT NOT NULL,
                    priority TEXT DEFAULT 'Normal',
                    created_by TEXT NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME
                )
            ");
            
            return $db;
        } catch (\PDOException $e) {
            // Fallback to JSON if SQLite fails
            return null;
        }
    }

    public static function getAll() {
        $db = self::getDB();
        if ($db) {
            $stmt = $db->query("SELECT * FROM tickets ORDER BY created_at DESC");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        
        // Fallback to JSON
        return self::getAllJSON();
    }

    public static function create($data) {
        $db = self::getDB();
        if ($db) {
            $ticket = [
                'id' => uniqid(),
                'title' => $data['title'],
                'description' => $data['description'] ?? '',
                'status' => $data['status'],
                'priority' => $data['priority'] ?? 'Normal',
                'created_by' => \App\Utils\Session::get('user_email'),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $stmt = $db->prepare("
                INSERT INTO tickets (id, title, description, status, priority, created_by, created_at)
                VALUES (:id, :title, :description, :status, :priority, :created_by, :created_at)
            ");
            
            $stmt->execute($ticket);
            return $ticket;
        }
        
        // Fallback to JSON
        return self::createJSON($data);
    }

    // ... Add similar update, delete, find methods for SQLite

    // JSON fallback methods
    private static function getAllJSON() {
        $file = __DIR__ . '/../../data/tickets.json';
        if (!file_exists($file)) {
            return [];
        }
        $data = file_get_contents($file);
        return json_decode($data, true) ?: [];
    }

    private static function createJSON($data) {
        $tickets = self::getAllJSON();
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
        file_put_contents(__DIR__ . '/../../data/tickets.json', json_encode($tickets, JSON_PRETTY_PRINT));
        return $ticket;
    }

    public static function update($id, $data) {
        $db = self::getDB();
        if ($db) {
            $stmt = $db->prepare("
                UPDATE tickets 
                SET title = :title, description = :description, status = :status, 
                    priority = :priority, updated_at = :updated_at 
                WHERE id = :id
            ");
            
            return $stmt->execute([
                'title' => $data['title'],
                'description' => $data['description'] ?? '',
                'status' => $data['status'],
                'priority' => $data['priority'] ?? 'Normal',
                'updated_at' => date('Y-m-d H:i:s'),
                'id' => $id
            ]);
        }
        
        // Fallback to JSON
        return self::updateJSON($id, $data);
    }

    public static function delete($id) {
        $db = self::getDB();
        if ($db) {
            $stmt = $db->prepare("DELETE FROM tickets WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        }
        
        // Fallback to JSON
        return self::deleteJSON($id);
    }

    public static function find($id) {
        $db = self::getDB();
        if ($db) {
            $stmt = $db->prepare("SELECT * FROM tickets WHERE id = :id");
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        }
        
        // Fallback to JSON
        return self::findJSON($id);
    }

    // JSON helper methods
    private static function updateJSON($id, $data) {
        $tickets = self::getAllJSON();
        foreach ($tickets as &$ticket) {
            if ($ticket['id'] === $id) {
                $ticket['title'] = $data['title'];
                $ticket['description'] = $data['description'] ?? '';
                $ticket['status'] = $data['status'];
                $ticket['priority'] = $data['priority'] ?? 'Normal';
                $ticket['updated_at'] = date('Y-m-d H:i:s');
                file_put_contents(__DIR__ . '/../../data/tickets.json', json_encode($tickets, JSON_PRETTY_PRINT));
                return true;
            }
        }
        return false;
    }

    private static function deleteJSON($id) {
        $tickets = self::getAllJSON();
        $tickets = array_filter($tickets, function($ticket) use ($id) {
            return $ticket['id'] !== $id;
        });
        file_put_contents(__DIR__ . '/../../data/tickets.json', json_encode(array_values($tickets), JSON_PRETTY_PRINT));
        return true;
    }

    private static function findJSON($id) {
        $tickets = self::getAllJSON();
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
        $inProgress = array_filter($tickets, function($t) { return $t['status'] === 'in_progress'; });

        return [
            'total' => $total,
            'open' => count($open),
            'resolved' => count($resolved),
            'in_progress' => count($inProgress)
        ];
    }
}