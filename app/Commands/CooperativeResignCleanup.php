<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\KopPengunduranDiriModel;
use App\Models\UserMessageModel;
use App\Models\AuditLogModel;
use App\Models\KopAnggotaModel;

class CooperativeResignCleanup extends BaseCommand
{
    protected $group       = 'Cooperative';
    protected $name        = 'cooperative:resign-cleanup';
    protected $description = 'Clean up all cooperative resignation requests pending for more than 30 days and mark them as expired.';

    public function run(array $params)
    {
        CLI::write('Memulai pemeriksaan pembersihan permohonan pengunduran diri...', 'blue');

        $resignModel = new KopPengunduranDiriModel();
        $messageModel = new UserMessageModel();
        $anggotaModel = new KopAnggotaModel();
        $db = \Config\Database::connect();

        // 30 days threshold
        $thresholdDate = date('Y-m-d H:i:s', strtotime('-30 days'));

        $pendingExpired = $resignModel->where('status', 'pending')
                                      ->where('created_at <', $thresholdDate)
                                      ->findAll();

        if (empty($pendingExpired)) {
            CLI::write('Tidak ada permohonan pengunduran diri yang kedaluwarsa untuk diproses.', 'yellow');
            return;
        }

        $totalProcessed = 0;

        foreach ($pendingExpired as $req) {
            $db->transBegin();
            try {
                // Update resignation status to expired
                $updateRes = $resignModel->update($req['id'], [
                    'status'     => 'expired',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

                if (!$updateRes) {
                    $db->transRollback();
                    CLI::write("-> [GAGAL] Gagal memperbarui status pengajuan ID {$req['id']} di database.", 'red');
                    continue;
                }

                // If DB transaction check fails
                if ($db->transStatus() === false) {
                    $db->transRollback();
                    CLI::write("-> [GAGAL] Gagal memperbarui status pengajuan ID {$req['id']} di database.", 'red');
                    continue;
                }

                $db->transCommit();
                CLI::write("-> [SUKSES] Pengajuan ID {$req['id']} diubah menjadi expired.", 'green');
                $totalProcessed++;

                // Logging audit
                $member = $anggotaModel->find($req['anggota_id']);
                $userId = $member ? $member['user_id'] : null;
                if ($userId) {
                    AuditLogModel::log('coop_resign_expired', "Permohonan pengunduran diri ID {$req['id']} kadaluarsa karena melebihi batas waktu 30 hari.", $userId);
                }

                // Best-effort Notification sending
                if ($userId) {
                    try {
                        $messageModel->insert([
                            'user_id'             => $userId,
                            'sender_id'           => null,
                            'invitation_id'       => null,
                            'subject'             => 'Permohonan Pengunduran Diri Anda Telah Kadaluarsa',
                            'message'             => 'Yth. Anggota,<br/><br/>Kami menginformasikan bahwa permohonan pengunduran diri yang Anda ajukan pada ' . date('d M Y, H:i', strtotime($req['created_at'])) . ' telah <strong>kadaluarsa (expired)</strong> karena melebihi batas waktu peninjauan 30 hari.<br/><br/>Jika Anda masih ingin mengundurkan diri dari keanggotaan koperasi, silakan kirimkan permohonan pengajuan baru melalui portal koperasi.<br/><br/>Salam,<br/>Pengurus KSP',
                            'type'                => 'general',
                            'is_read'             => 0,
                            'action_taken'        => 0,
                            'deleted_by_sender'   => 0,
                            'deleted_by_receiver' => 0,
                        ]);
                    } catch (\Throwable $e) {
                        log_message('error', "Gagal mengirimkan notifikasi inbox expired untuk user {$userId}: " . $e->getMessage());
                    }
                }

            } catch (\Throwable $e) {
                $db->transRollback();
                CLI::write("-> [ERROR] Galat sistem memproses pengajuan ID {$req['id']}: " . $e->getMessage(), 'red');
            }
        }

        CLI::write("Pembersihan selesai! Total pengajuan kedaluwarsa diproses: {$totalProcessed}.", 'green');
    }
}
