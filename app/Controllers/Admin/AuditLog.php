<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AuditLogModel;

class AuditLog extends BaseController
{
    /**
     * Render the administrative and security audit logs screen.
     */
    public function auditLogs()
    {
        return view('admin/audit_logs', [
            'title' => 'Log Audit Keamanan Sistem',
        ]);
    }

    /**
     * AJAX Endpoint for DataTables
     */
    public function getLogsData()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Akses ditolak']);
        }

        $auditModel = new AuditLogModel();
        $postData = $this->request->getPost();
        
        $list = $auditModel->getDatatables($postData);
        $data = [];
        
        // Load helper for action labels mapping
        $actionLabels = [
            'impersonation_start'       => 'Mulai Impersonasi',
            'impersonation_stop'        => 'Selesai Impersonasi',
            'user_blocked'              => 'Pengguna Diblokir',
            'user_activated'            => 'Pengguna Diaktifkan',
            'role_changed'              => 'Ubah Peran',
            'user_deleted'              => 'Pengguna Dihapus',
            'user_created'              => 'Pengguna Dibuat',
            'user_imported'             => 'Impor Pengguna',
            'coop_saving_approved'      => 'Simpanan Disetujui',
            'coop_saving_rejected'      => 'Simpanan Ditolak',
            'coop_loan_approved'        => 'Pinjaman Disetujui',
            'coop_loan_rejected'        => 'Pinjaman Ditolak',
            'coop_installment_approved' => 'Angsuran Disetujui',
            'coop_installment_rejected' => 'Angsuran Ditolak',
            'coop_member_toggled'       => 'Status Anggota Diubah',
            'coop_invitation_created'   => 'Undangan Dibuat',
            'coop_invitation_deleted'   => 'Undangan Dihapus',
            'coop_invitation_rejected'  => 'Undangan Ditolak',
            'coop_setting_changed'      => 'Pengaturan KSP Diubah',
            'coop_broadcast'            => 'Pesan Broadcast',
            'coop_direct_loan'          => 'Pinjaman Langsung',
            'coop_fund_stored'          => 'Dana Kas Dicatat',
            'coop_shu_distributed'      => 'SHU Didistribusikan',
            'admin_login'               => 'Login Admin',
            'audit_cleared'             => 'Log Audit Dihapus',
        ];

        foreach ($list as $log) {
            $row = [];
            
            // Kolom 1: Waktu
            $row[] = '<div class="text-xs text-slate-400 font-medium whitespace-nowrap">' . date('d M Y, H:i:s', strtotime($log['created_at'])) . '</div>';
            
            // Kolom 2: User
            if ($log['username']) {
                $initial = esc(strtoupper(substr($log['username'], 0, 2)));
                $row[] = '<div class="flex items-center gap-2"><div class="w-6 h-6 rounded-md bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 font-bold text-[10px] flex items-center justify-center shrink-0">' . $initial . '</div><span class="font-bold text-white text-xs">' . esc($log['username']) . '</span></div>';
            } else {
                $row[] = '<span class="text-slate-500 italic text-xs">Sistem (Otomatis)</span>';
            }
            
            // Kolom 3: Aksi
            $actionLabel = $actionLabels[$log['action']] ?? ucwords(str_replace('_', ' ', $log['action']));
            $badgeColor = 'bg-slate-500/10 text-slate-400 border-slate-500/20';
            $act = $log['action'];
            
            if (strpos($act, 'impersonation_start') !== false) {
                $badgeColor = 'bg-amber-500/10 text-amber-400 border-amber-500/20';
            } elseif (strpos($act, 'impersonation_stop') !== false) {
                $badgeColor = 'bg-blue-500/10 text-blue-400 border-blue-500/20';
            } elseif (strpos($act, 'blocked') !== false || strpos($act, 'rejected') !== false || strpos($act, 'deleted') !== false || strpos($act, 'cleared') !== false) {
                $badgeColor = 'bg-rose-500/10 text-rose-400 border-rose-500/20';
            } elseif (strpos($act, 'activated') !== false || strpos($act, 'approved') !== false) {
                $badgeColor = 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
            } elseif (strpos($act, 'role_changed') !== false || strpos($act, 'setting') !== false) {
                $badgeColor = 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20';
            } elseif (strpos($act, 'broadcast') !== false || strpos($act, 'invitation') !== false) {
                $badgeColor = 'bg-purple-500/10 text-purple-400 border-purple-500/20';
            }
            
            $row[] = '<span class="inline-flex px-2 py-0.5 text-[10px] font-bold rounded-md border ' . $badgeColor . ' tracking-wider whitespace-nowrap">' . esc($actionLabel) . '</span>';
            
            // Kolom 4: Detail
            $row[] = '<div class="text-xs text-slate-300 font-medium leading-relaxed max-w-sm">' . esc($log['details']) . '</div>';
            
            // Kolom 5: IP
            $row[] = '<div class="text-center text-[10px] text-slate-500 font-semibold font-mono whitespace-nowrap">' . esc($log['ip_address'] ?? '-') . '</div>';
            
            $data[] = $row;
        }

        return $this->response->setJSON([
            "draw"            => $postData['draw'] ?? 1,
            "recordsTotal"    => $auditModel->countAllData(),
            "recordsFiltered" => $auditModel->countFiltered($postData),
            "data"            => $data,
        ]);
    }

    /**
     * Clear all logs with auto PDF Backup and Broadcast
     */
    public function clearLogs()
    {
        // Require superadmin role
        if (!auth()->user()->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Akses Ditolak: Hanya Super Admin yang berhak menghapus Log Audit.');
        }

        $auditModel = new AuditLogModel();
        
        // 1. Ambil seluruh data log sebelum dihapus
        $logs = $auditModel->select('audit_logs.*, users.username')
            ->join('users', 'users.id = audit_logs.user_id', 'left')
            ->orderBy('audit_logs.created_at', 'ASC')
            ->findAll();

        if (empty($logs)) {
            return redirect()->back()->with('error', 'Tidak ada log untuk dihapus.');
        }

        // 2. Generate PDF menggunakan Dompdf
        $dompdf = new \Dompdf\Dompdf();
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');
        $dompdf->setOptions($options);

        $html = view('admin/pdf_audit_backup', [
            'logs' => $logs,
            'clearedBy' => auth()->user()->username
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // 3. Simpan PDF ke folder uploads
        $backupDir = FCPATH . 'uploads/audit_backups';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0777, true);
        }
        $filename = 'audit_backup_' . date('Ymd_His') . '.pdf';
        $filepath = $backupDir . '/' . $filename;
        file_put_contents($filepath, $dompdf->output());

        // 4. Hapus log dari database
        $auditModel->truncate();

        // 5. Catat aktivitas penghapusan
        AuditLogModel::log('audit_cleared', 'Seluruh log audit telah dihapus. File backup PDF telah dibuat dan didistribusikan.', auth()->id());

        // 6. Broadcast ke anggota koperasi melalui MessageController (Internal logic)
        $userModel = new \App\Models\UserModel();
        $members = $userModel->findAll(); // Send to all registered users (or limit to anggota)
        
        $messageModel = new \App\Models\UserMessageModel();
        $pdfLink = base_url('uploads/audit_backups/' . $filename);
        $broadcastMessage = "Yth. Anggota,<br><br>Kami menginformasikan bahwa Pengurus Koperasi baru saja melakukan pembersihan rutin pada Log Audit Sistem. Sebagai bentuk transparansi dan untuk mencegah penyalahgunaan wewenang (anti-kecurangan), sistem telah mencetak salinan seluruh riwayat log aktivitas sebelum dihapus.<br><br>Anda dapat mengunduh dan meninjau berkas backup tersebut melalui tautan berikut: <br><br><a href='{$pdfLink}' style='display:inline-block;padding:10px 24px;background:#4f46e5;color:#fff;font-weight:700;border-radius:10px;text-decoration:none;' target='_blank'>Unduh Dokumen Audit PDF</a><br><br>Salam Transparansi,<br>Sistem Automasi Koperasi";

        $db = \Config\Database::connect();
        $db->transStart();
        
        foreach ($members as $mem) {
            $messageModel->insert([
                'user_id'       => $mem->id,
                'sender_id'     => auth()->id(),
                'subject'       => 'Laporan Transparansi: Backup Log Audit Sistem',
                'message'       => $broadcastMessage,
                'type'          => 'system',
                'is_read'       => 0,
            ]);
        }
        
        $db->transComplete();

        return redirect()->back()->with('message', 'Log Audit berhasil dihapus! Backup PDF telah dikirim ke semua pengguna/anggota via Pesan Internal.');
    }

    /**
     * Download manual CSV backup
     */
    public function backupLogs()
    {
        $auditModel = new AuditLogModel();
        $logs = $auditModel->select('audit_logs.*, users.username')
            ->join('users', 'users.id = audit_logs.user_id', 'left')
            ->orderBy('audit_logs.created_at', 'ASC')
            ->findAll();

        $filename = 'audit_logs_backup_' . date('Y-m-d_H-i-s') . '.csv';

        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$filename");
        header("Content-Type: application/csv; "); 
        
        $file = fopen('php://output', 'w');
        
        $header = ['ID', 'Waktu Kejadian', 'Username', 'Aksi', 'Rincian', 'IP Address'];
        fputcsv($file, $header);
        
        foreach ($logs as $log) {
            $line = [
                $log['id'],
                $log['created_at'],
                $log['username'] ?? 'Sistem',
                $log['action'],
                $log['details'],
                $log['ip_address']
            ];
            fputcsv($file, $line);
        }
        
        fclose($file);
        exit;
    }
}
