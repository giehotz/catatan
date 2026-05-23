<?php

namespace App\Entities;

use CodeIgniter\Shield\Entities\User as ShieldUser;

class User extends ShieldUser
{
    /**
     * Get user initials from username.
     */
    public function getInitials(): string
    {
        return strtoupper(substr((string) $this->username, 0, 2));
    }

    /**
     * Get formatted created_at date.
     */
    public function getCreatedAtFormatted(): string
    {
        return $this->created_at ? date('d M Y, H:i', strtotime($this->created_at)) : '-';
    }

    /**
     * Get a comma-separated string of roles.
     */
    public function getRoleString(): string
    {
        $groups = $this->getGroups();
        return !empty($groups) ? implode(', ', array_map('ucfirst', $groups)) : 'User';
    }

    /**
     * Check if user is only a 'user' (and not a manager).
     */
    public function isOnlyUser(): bool
    {
        return $this->inGroup('user') && !$this->inGroup('manager');
    }

    /**
     * Check if user is a manager.
     */
    public function isManager(): bool
    {
        return $this->inGroup('manager');
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->inGroup('admin');
    }
}
