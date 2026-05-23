<?php

if (!function_exists('count_unread_messages')) {
    /**
     * Get the count of unread messages for the currently logged-in user
     */
    function count_unread_messages(int $userId = null): int
    {
        if ($userId === null) {
            $userId = auth()->id();
        }

        if (!$userId) {
            return 0;
        }

        $messageModel = new \App\Models\UserMessageModel();
        return $messageModel->where('user_id', $userId)
            ->where('is_read', 0)
            ->where('deleted_by_receiver', 0)
            ->countAllResults();
    }
}
