<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Notification extends Model
{
    public function recent(int $userId, string $search = '', string $type = ''): array
    {
        $query = $this->table('notifications n')
            ->select('n.*', 'sender.name AS sender_name')
            ->leftJoin('users sender', 'sender.id', '=', 'n.sender_id')
            ->where('n.user_id', $userId)
            ->whereNull('n.deleted_at')
            ->whereRaw('(n.title LIKE ? OR n.description LIKE ?)', ['%' . $search . '%', '%' . $search . '%']);

        if ($type !== '') {
            $query->where('n.notification_type', $type);
        }

        return $query->orderBy('n.created_at', 'DESC')->limit(50)->get();
    }

    public function unreadCount(int $userId): int
    {
        return $this->table('notifications')
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->whereNull('deleted_at')
            ->count();
    }

    public function types(): array
    {
        return $this->table('notifications')
            ->select('DISTINCT notification_type')
            ->orderBy('notification_type')
            ->get();
    }

    public function create(int $userId, ?int $senderId, string $type, string $title, string $description = '', string $url = ''): void
    {
        $this->table('notifications')->insert([
            'user_id' => $userId,
            'sender_id' => $senderId,
            'notification_type' => $type,
            'title' => $title,
            'description' => $description,
            'related_url' => $url,
        ]);
    }

    public function markRead(int $userId, int $id): void
    {
        $this->table('notifications')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->update(['read_at' => date('Y-m-d H:i:s')]);
    }

    public function markAllRead(int $userId): void
    {
        $this->table('notifications')
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => date('Y-m-d H:i:s')]);
    }

    public function delete(int $userId, int $id): void
    {
        $this->table('notifications')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->update(['deleted_at' => date('Y-m-d H:i:s')]);
    }
}
