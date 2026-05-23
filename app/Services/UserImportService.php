<?php

namespace App\Services;

use App\Entities\User;
use PhpOffice\PhpSpreadsheet\IOFactory;

class UserImportService
{
    /**
     * Import users from an Excel file using CodeIgniter Shield.
     *
     * @param string $filePath Path to the uploaded Excel file.
     * @return array Array containing 'success_count' and 'errors' array.
     */
    public function importFromExcel(string $filePath): array
    {
        // Load the Excel file
        try {
            $spreadsheet = IOFactory::load($filePath);
        } catch (\Exception $e) {
            return [
                'success_count' => 0,
                'errors'        => ['Gagal membaca file Excel: ' . $e->getMessage()]
            ];
        }

        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestDataRow();

        $userModel = auth()->getProvider();

        $successCount = 0;
        $errors = [];

        // Assuming row 1 contains headers: Username, Email, Password, Role
        for ($row = 2; $row <= $highestRow; $row++) {
            $username = trim((string) $worksheet->getCell('A' . $row)->getValue());
            $email    = trim((string) $worksheet->getCell('B' . $row)->getValue());
            $password = (string) $worksheet->getCell('C' . $row)->getValue();
            $role     = strtolower(trim((string) $worksheet->getCell('D' . $row)->getValue()));

            // Skip empty rows
            if (empty($username) && empty($email)) {
                continue;
            }

            // Basic validation
            if (empty($username) || empty($email) || empty($password)) {
                $errors[] = "Baris {$row}: Username, Email, dan Password wajib diisi.";
                continue;
            }

            // Prepare Shield User Entity
            // Using custom App\Entities\User if available, otherwise Shield's default
            $user = new User([
                'username' => $username,
                'email'    => $email,
                'password' => $password,
                'active'   => 1, // Activate immediately
            ]);

            // Attempt to save
            if (!$userModel->save($user)) {
                // Collect validation errors from Shield
                $validationErrors = $userModel->errors();
                $errorMsg = is_array($validationErrors) ? implode(', ', $validationErrors) : 'Unknown validation error';
                $errors[] = "Baris {$row} ({$username}): {$errorMsg}";
                continue;
            }

            // Assign role (default to 'user' if empty)
            if (empty($role)) {
                $role = 'user';
            }

            // We must retrieve the newly inserted user to use the addGroup method properly
            $insertedUser = $userModel->findById($userModel->getInsertID());
            
            if ($insertedUser) {
                try {
                    $insertedUser->addGroup($role);
                } catch (\Exception $e) {
                    $errors[] = "Baris {$row} ({$username}): Gagal menetapkan peran '{$role}'.";
                }
            }

            $successCount++;
        }

        return [
            'success_count' => $successCount,
            'errors'        => $errors,
        ];
    }
}
