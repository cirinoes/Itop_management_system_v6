<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Message extends Model
{
    public function conversations(int $userId, string $search = ''): array
    {
        $like = '%' . $search . '%';
        $stmt = $this->db->prepare('SELECT c.*, MAX(m.created_at) AS last_message_at, SUM(m.sender_id <> ? AND m.read_at IS NULL AND m.deleted_at IS NULL) AS unread_count, GROUP_CONCAT(DISTINCT u.name ORDER BY u.name SEPARATOR ", ") AS participants FROM conversations c JOIN conversation_participants cp ON cp.conversation_id = c.id AND cp.user_id = ? AND cp.deleted_at IS NULL JOIN conversation_participants allp ON allp.conversation_id = c.id JOIN users u ON u.id = allp.user_id LEFT JOIN messages m ON m.conversation_id = c.id AND m.deleted_at IS NULL WHERE c.subject LIKE ? OR u.name LIKE ? GROUP BY c.id ORDER BY COALESCE(MAX(m.created_at), c.created_at) DESC');
        $stmt->execute([$userId, $userId, $like, $like]);
        return $stmt->fetchAll();
    }

    public function messages(int $conversationId, int $userId): array
    {
        $check = $this->table('conversation_participants')
            ->where('conversation_id', $conversationId)
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->count();
            
        if (!$check) {
            return [];
        }

        $this->table('messages')
            ->where('conversation_id', $conversationId)
            ->whereRaw('sender_id <> ?', [$userId])
            ->whereNull('read_at')
            ->update(['read_at' => date('Y-m-d H:i:s')]);
            
        return $this->table('messages m')
            ->select('m.*', 'u.name AS sender_name', 'u.profile_picture')
            ->join('users u', 'u.id', '=', 'm.sender_id')
            ->where('m.conversation_id', $conversationId)
            ->whereNull('m.deleted_at')
            ->orderBy('m.created_at', 'ASC')
            ->get();
    }

    public function contacts(int $userId): array
    {
        return $this->table('users')
            ->select('users.id', 'users.name', 'users.email', 'roles.slug AS role_slug')
            ->join('roles', 'roles.id', '=', 'users.role_id')
            ->whereRaw('users.id <> ?', [$userId])
            ->where('users.status', 'active')
            ->orderBy('roles.id', 'ASC, users.name')
            ->get();
    }

    public function unreadCount(int $userId): int
    {
        return $this->table('messages m')
            ->join('conversation_participants cp', 'cp.conversation_id', '=', 'm.conversation_id')
            ->where('cp.user_id', $userId)
            ->whereRaw('m.sender_id <> ?', [$userId])
            ->whereNull('m.read_at')
            ->whereNull('m.deleted_at')
            ->whereNull('cp.deleted_at')
            ->count();
    }

    public function start(int $senderId, int $receiverId, string $subject, string $body, ?string $attachment = null): int
    {
        $this->db->beginTransaction();
        $stmt = $this->db->prepare('INSERT INTO conversations (subject, created_by) VALUES (?, ?)');
        $stmt->execute([$subject ?: 'Conversation', $senderId]);
        $conversationId = (int) $this->db->lastInsertId();
        $participant = $this->db->prepare('INSERT INTO conversation_participants (conversation_id, user_id) VALUES (?, ?)');
        $participant->execute([$conversationId, $senderId]);
        $participant->execute([$conversationId, $receiverId]);
        $message = $this->db->prepare('INSERT INTO messages (conversation_id, sender_id, body, attachment_path) VALUES (?, ?, ?, ?)');
        $message->execute([$conversationId, $senderId, $body, $attachment]);
        $this->db->commit();
        return $conversationId;
    }

    public function reply(int $conversationId, int $senderId, string $body, ?string $attachment = null): void
    {
        $stmt = $this->db->prepare('INSERT INTO messages (conversation_id, sender_id, body, attachment_path) SELECT ?, ?, ?, ? WHERE EXISTS (SELECT 1 FROM conversation_participants WHERE conversation_id = ? AND user_id = ? AND deleted_at IS NULL)');
        $stmt->execute([$conversationId, $senderId, $body, $attachment, $conversationId, $senderId]);
    }

    public function deleteMessage(int $messageId, int $senderId): void
    {
        $this->table('messages')
            ->where('id', $messageId)
            ->where('sender_id', $senderId)
            ->update(['deleted_at' => date('Y-m-d H:i:s')]);
    }
}
