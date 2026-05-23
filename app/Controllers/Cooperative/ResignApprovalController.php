<?php

namespace App\Controllers\Cooperative;

use App\Controllers\BaseController;
use App\Models\KopPengunduranDiriModel;
use App\Models\KopAnggotaModel;
use App\Models\KopSimpananModel;
use App\Models\KopPinjamanModel;
use App\Models\KopAngsuranModel;
use App\Models\AuditLogModel;
use App\Models\UserMessageModel;

class ResignApprovalController extends BaseController
{
    protected KopPengunduranDiriModel $resignModel;
    protected KopAnggotaModel $anggotaModel;
    protected KopSimpananModel $simpananModel;
    protected KopPinjamanModel $pinjamanModel;
    protected KopAngsuranModel $angsuranModel;
    protected UserMessageModel $messageModel;

    public function __construct()
    {
        $this->resignModel = new KopPengunduranDiriModel();
        $this->anggotaModel = new KopAnggotaModel();
        $this->simpananModel = new KopSimpananModel();
        $this->pinjamanModel = new KopPinjamanModel();
        $this->angsuranModel = new KopAngsuranModel();
        $this->messageModel = new UserMessageModel();
    }

    /**
     * Tampilkan antrean permohonan pengunduran diri koperasi.
     */
    public function index()
    {
        // 1. Double-check otorisasi
        if (!auth()->user()->inGroup('admin', 'superadmin', 'manager')) {
            return redirect()->to(base_url('admin'))->with('error', 'Akses Ditolak.');
        }

        // 2. Event-driven fallback: Auto-expire pending requests > 30 days (limited to 50 for UI performance)
        $thresholdDate = date('Y-m-d H:i:s', strtotime('-30 days'));
        $pendingExpired = $this->resignModel->where('status', 'pending')
                                            ->where('created_at <', $thresholdDate)
                                            ->limit(50)
                                            ->findAll();
        
        if (!empty($pendingExpired)) {
            $db = \Config\Database::connect();
            foreach ($pendingExpired as $req) {
                $db->transBegin();
                try {
                    $updateRes = $this->resignModel->update($req['id'], [
                        'status'     => 'expired',
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);

                    if ($updateRes && $db->transStatus() !== false) {
                        $db->transCommit();
                        
                        $member = $this->anggotaModel->find($req['anggota_id']);
                        $userId = $member ? $member['user_id'] : null;
                        if ($userId) {
                            AuditLogModel::log('coop_resign_expired', "Permohonan pengunduran diri ID {$req['id']} otomatis kedaluwarsa via dashboard pengurus.", $userId);
                            
                            // Send Inbox notification (Best-effort)
                            try {
                                $this->messageModel->insert([
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
                    } else {
                        $db->transRollback();
                    }
                } catch (\Throwable $e) {
                    $db->transRollback();
                    log_message('error', "Fallback auto-expire failed for Resign ID {$req['id']}: " . $e->getMessage());
                }
            }
        }

        // 3. Fetch all resignation requests with member details
        $rawRequests = $this->resignModel
            ->select('kop_pengunduran_diri.*, kop_anggota.nomor_anggota, users.username, users.email')
            ->join('kop_anggota', 'kop_anggota.id = kop_pengunduran_diri.anggota_id')
            ->join('users', 'users.id = kop_anggota.user_id')
            ->orderBy('kop_pengunduran_diri.created_at', 'DESC')
            ->findAll();

        $processedRequests = [];

        // Loop and enrich pending requests with dynamic financial statuses
        foreach ($rawRequests as $req) {
            $anggotaId = $req['anggota_id'];

            // Calculate current savings balances
            $savings = $this->simpananModel->where('anggota_id', $anggotaId)->where('status', 'approved')->findAll();
            $saldoPokok = 0; $saldoWajib = 0; $saldoSukarela = 0;
            foreach ($savings as $s) {
                $nominal = floatval($s['nominal']);
                if ($s['tipe_transaksi'] === 'setoran') {
                    if ($s['jenis_simpanan'] === 'pokok') $saldoPokok += $nominal;
                    if ($s['jenis_simpanan'] === 'wajib') $saldoWajib += $nominal;
                    if ($s['jenis_simpanan'] === 'sukarela') $saldoSukarela += $nominal;
                } else {
                    if ($s['jenis_simpanan'] === 'sukarela') $saldoSukarela -= $nominal;
                    if ($s['jenis_simpanan'] === 'pokok') $saldoPokok -= $nominal;
                    if ($s['jenis_simpanan'] === 'wajib') $saldoWajib -= $nominal;
                }
            }
            $totalSimpanan = $saldoPokok + $saldoWajib + $saldoSukarela;

            // Calculate current active loan debts
            $activeLoans = $this->pinjamanModel->where('anggota_id', $anggotaId)->whereIn('status', ['approved', 'paid'])->findAll();
            $totalDebtLimit = 0;
            $totalPaidInstallments = 0;
            foreach ($activeLoans as $l) {
                $totalDebtLimit += floatval($l['nominal_total']);
                $paid = $this->angsuranModel->where('pinjaman_id', $l['id'])->where('status', 'approved')->selectSum('nominal_bayar')->first()['nominal_bayar'] ?? 0;
                $totalPaidInstallments += floatval($paid);
            }
            $sisaPinjaman = $totalDebtLimit - $totalPaidInstallments;

            $req['saldo_pokok'] = $saldoPokok;
            $req['saldo_wajib'] = $saldoWajib;
            $req['saldo_sukarela'] = $saldoSukarela;
            $req['total_simpanan'] = $totalSimpanan;
            $req['sisa_pinjaman'] = $sisaPinjaman;

            $processedRequests[] = $req;
        }

        return view('admin/cooperative/resign_requests', [
            'title'    => 'Persetujuan Pengunduran Diri Anggota',
            'requests' => $processedRequests,
        ]);
    }

    /**
     * Setujui permohonan pengunduran diri (Approved).
     */
    public function approve(int $id)
    {
        try {
            if (!auth()->user()->inGroup('admin', 'superadmin', 'manager')) {
            return redirect()->to(base_url('admin'))->with('error', 'Akses Ditolak.');
        }

        $request = $this->resignModel->find($id);
        if (!$request) {
            return redirect()->back()->with('error', 'Permohonan pengunduran diri tidak ditemukan.');
        }

        if ($request['status'] !== 'pending') {
            return redirect()->back()->with('error', 'Permohonan ini sudah diproses sebelumnya.');
        }

        $anggotaId = $request['anggota_id'];
        $member = $this->anggotaModel->find($anggotaId);
        if (!$member) {
            return redirect()->back()->with('error', 'Data anggota koperasi tidak valid.');
        }

        // 1. Verifikasi sisa pinjaman/utang wajib Rp 0
        $activeLoans = $this->pinjamanModel->where('anggota_id', $anggotaId)->whereIn('status', ['approved', 'paid'])->findAll();
        $totalDebtLimit = 0;
        $totalPaidInstallments = 0;
        foreach ($activeLoans as $l) {
            $totalDebtLimit += floatval($l['nominal_total']);
            $paid = $this->angsuranModel->where('pinjaman_id', $l['id'])->where('status', 'approved')->selectSum('nominal_bayar')->first()['nominal_bayar'] ?? 0;
            $totalPaidInstallments += floatval($paid);
        }
        $sisaPinjaman = $totalDebtLimit - $totalPaidInstallments;

        if ($sisaPinjaman > 0) {
            return redirect()->back()->with('error', 'Persetujuan ditolak: Anggota masih memiliki sisa pinjaman kredit koperasi yang belum lunas (Rp ' . number_format($sisaPinjaman, 2, ',', '.') . ').');
        }

        // Calculate current balances to prepare financial balancing/withdrawal transactions
        $savings = $this->simpananModel->where('anggota_id', $anggotaId)->where('status', 'approved')->findAll();
        $saldoPokok = 0; $saldoWajib = 0; $saldoSukarela = 0;
        foreach ($savings as $s) {
            $nominal = floatval($s['nominal']);
            if ($s['tipe_transaksi'] === 'setoran') {
                if ($s['jenis_simpanan'] === 'pokok') $saldoPokok += $nominal;
                if ($s['jenis_simpanan'] === 'wajib') $saldoWajib += $nominal;
                if ($s['jenis_simpanan'] === 'sukarela') $saldoSukarela += $nominal;
            } else {
                if ($s['jenis_simpanan'] === 'sukarela') $saldoSukarela -= $nominal;
                if ($s['jenis_simpanan'] === 'pokok') $saldoPokok -= $nominal;
                if ($s['jenis_simpanan'] === 'wajib') $saldoWajib -= $nominal;
            }
        }

        // 2. DB Manual Transaction wrapped in a 3x Concurrency Retry Loop with Exponential Backoff
        $db = \Config\Database::connect();
        
        $maxRetries = 3;
        $retryCount = 0;
        $success = false;
        $nomorSurat = '';
        $nextUrut = 1;

        while ($retryCount < $maxRetries) {
            $db->transBegin();
            try {
                $year = date('Y');

                // 3. Pessimistic Locking FOR UPDATE on nomor_urut (atomic InnoDB row lock)
                $row = $db->query("SELECT MAX(nomor_urut) AS max_urut FROM kop_pengunduran_diri WHERE YEAR(created_at) = ? FOR UPDATE", [$year])->getRowArray();
                $nextUrut = ($row['max_urut'] ?? 0) + 1;
                
                // Use whitelisted dynamic formatter helper
                $nomorSurat = \App\Helpers\SuratHelper::generateNomorSurat($nextUrut, 'RE');

                // 4. Generate Cryptographic Random Salt & SHA-256 HMAC Hash
                $salt = bin2hex(random_bytes(16));
                $hashPayload = $anggotaId . '|' . $nomorSurat . '|' . $salt;
                $hashVerifikasi = hash_hmac('sha256', $hashPayload, 'secret-resignation-key-' . $salt);

                // 5. Update Status Pengajuan Resign
                $updateResign = $this->resignModel->update($id, [
                    'status'           => 'approved',
                    'nomor_surat'      => $nomorSurat,
                    'nomor_urut'       => $nextUrut,
                    'hash_verifikasi'  => $hashVerifikasi,
                    'crypt_salt'       => $salt,
                    'processed_by'     => auth()->id(),
                    'processed_at'     => date('Y-m-d H:i:s'),
                    'updated_at'       => date('Y-m-d H:i:s'),
                ]);

                if (!$updateResign) {
                    throw new \RuntimeException('Gagal memperbarui status permohonan pengunduran diri.');
                }

                // 6. Update Status Keanggotaan di kop_anggota menjadi 'nonaktif'
                $updateAnggota = $this->anggotaModel->update($anggotaId, [
                    'status_keaktifan' => 'nonaktif'
                ]);

                if (!$updateAnggota) {
                    throw new \RuntimeException('Gagal memperbarui status keaktifan anggota.');
                }

                // 7. Write Polymorphic Snapshot (Freezes KOP & Signer visually forever)
                $snapshotModel = new \App\Models\KopDocumentSnapshotModel();
                
                $kopData = [
                    'schema_version'   => 1,
                    'cooperative_name' => \App\Models\KopSettingModel::getSetting('kop_nama_koperasi'),
                    'legal_id'         => \App\Models\KopSettingModel::getSetting('kop_badan_hukum'),
                    'work_region'      => \App\Models\KopSettingModel::getSetting('kop_wilayah_kerja'),
                    'address'          => \App\Models\KopSettingModel::getSetting('kop_alamat'),
                    'phone'            => \App\Models\KopSettingModel::getSetting('kop_telepon'),
                    'email'            => \App\Models\KopSettingModel::getSetting('kop_email'),
                    'website'          => \App\Models\KopSettingModel::getSetting('kop_website'),
                    'logo_path'        => \App\Models\KopSettingModel::getSetting('kop_logo_path'),
                ];
                
                // Fallback to baseline Config\LetterFallback if empty
                $fallback = new \Config\LetterFallback();
                if (empty($kopData['cooperative_name'])) {
                    $kopData['cooperative_name'] = $fallback->cooperativeName;
                    $kopData['legal_id']         = $fallback->legalId;
                    $kopData['work_region']      = $fallback->workRegion;
                    $kopData['address']          = $fallback->address;
                    $kopData['phone']            = $fallback->phone;
                    $kopData['email']            = $fallback->email;
                    $kopData['website']          = $fallback->website;
                    $kopData['logo_path']        = $fallback->logoPath;
                }

                $signerData = \App\Helpers\SuratHelper::getSigner('resign');

                $formatData = [
                    'schema_version' => 1,
                    'format_string'  => \App\Models\KopSettingModel::getSetting('kop_format_nomor_surat', '{nomor_urut}/KOP-SKP/{kode}/{year}'),
                    'letter_code'    => 'RE',
                    'unit_code'      => \App\Models\KopSettingModel::getSetting('kop_unit_code', 'PST')
                ];

                // Write polymorphic snapshot
                $snapshotModel->insert([
                    'document_type'   => 'resign',
                    'document_id'     => $id,
                    'kop_snapshot'    => json_encode($kopData),
                    'signer_snapshot' => json_encode($signerData),
                    'format_snapshot' => json_encode($formatData),
                ]);

                // 8. Automasi Finansial: Catat penarikan sisa simpanan ke Rp 0
                $simpananTypes = [
                    'pokok'    => $saldoPokok,
                    'wajib'    => $saldoWajib,
                    'sukarela' => $saldoSukarela,
                ];

                foreach ($simpananTypes as $jenis => $saldo) {
                    if ($saldo > 0) {
                        $insertSimpanan = $this->simpananModel->insert([
                            'anggota_id'        => $anggotaId,
                            'jenis_simpanan'    => $jenis,
                            'tipe_transaksi'    => 'penarikan',
                            'nominal'           => $saldo,
                            'status'            => 'approved',
                            'bukti_transfer'    => null,
                            'keterangan'        => 'Pencairan sisa simpanan atas pengunduran diri resmi.',
                            'approved_by'       => auth()->id(),
                            'approved_at'       => date('Y-m-d H:i:s'),
                            'tanggal_transaksi' => date('Y-m-d H:i:s'),
                        ]);

                        if (!$insertSimpanan) {
                            throw new \RuntimeException("Gagal mencatat pencairan saldo simpanan {$jenis}.");
                        }
                    }
                }

                if ($db->transStatus() === false) {
                    throw new \RuntimeException('Transaksi database terindikasi gagal.');
                }

                $db->transCommit();
                $success = true;
                break; // Exit lock-retry loop on success
            } catch (\Throwable $e) {
                $db->transRollback();
                $retryCount++;
                if ($retryCount >= $maxRetries) {
                    // Log the final failure critically
                    log_message('critical', "LOCK FAILURE: Permohonan pengunduran diri ID {$id} gagal memperoleh kunci transaksi atau menyimpan data setelah {$maxRetries} kali percobaan. Kesalahan: " . $e->getMessage());
                    throw new \App\Exceptions\TransactionLockException("Gagal mengunci counter pengurutan atau menulis snapshot terverifikasi.", 0, $e);
                }
                // Exponential backoff sleep (50ms, 100ms)
                usleep(50000 * $retryCount);
            }
        }

        if (!$success) {
            return redirect()->back()->with('error', 'Terjadi kesalahan transaksi pengurutan.');
        }

        try {
            // 9. Invalidasi cache keanggotaan
            $userId = $member['user_id'];
            session()->remove('coop_member_active');
            session()->remove('coop_member_active_expires');
            cache()->delete("coop_member_active_{$userId}");

            // 10. Kirim Notifikasi Inbox dengan Link Surat Terverifikasi
            $subject = 'Pengunduran Diri Disetujui - Surat Keputusan Resmi Diterbitkan';
            $letterUrl = base_url('cooperative/resign/letter/' . $id);
            $message = "Yth. Anggota,<br/><br/>Kami menginformasikan bahwa permohonan pengunduran diri Anda dari keanggotaan koperasi telah <strong>DISETUJUI</strong> oleh pengurus koperasi dengan nomor surat resmi <strong>" . $nomorSurat . "</strong>.<br/><br/>Sebagai konsekuensi keputusan ini, seluruh sisa simpanan pokok, wajib, dan sukarela Anda telah dicairkan sepenuhnya (saldo akhir Rp 0) dan status keaktifan koperasi Anda kini resmi diubah menjadi <strong>Nonaktif</strong>.<br/><br/>Surat Keputusan Pengunduran Diri Resmi Anda telah diterbitkan secara digital. Anda dapat mengunduh dokumen tersebut melalui tautan berikut:<br/><a href=\"{$letterUrl}\" target=\"_blank\" style=\"display:inline-block; padding:8px 16px; background-color:#6366f1; color:#ffffff; text-decoration:none; border-radius:8px; font-weight:bold; font-size:14px; margin-top:8px;\">Unduh Surat Keputusan SK Resmi</a><br/><br/>Terima kasih atas segala kontribusi Anda selama menjadi bagian dari koperasi kami.<br/><br/>Salam,<br/>Pengurus KSP";

            $this->messageModel->insert([
                'user_id'             => $userId,
                'sender_id'           => auth()->id(),
                'invitation_id'       => null,
                'subject'             => $subject,
                'message'             => $message,
                'type'                => 'general',
                'is_read'             => 0,
                'action_taken'        => 0,
                'deleted_by_sender'   => 0,
                'deleted_by_receiver' => 0,
            ]);
        } catch (\Throwable $e) {
            log_message('error', "Gagal mengirimkan notifikasi inbox approved untuk user {$userId}: " . $e->getMessage());
        }

        // Audit logging
        $userObj = auth()->getProvider()->find($userId);
        $username = $userObj ? $userObj->username : 'Unknown';
        AuditLogModel::log('coop_resign_approved', "Menyetujui pengunduran diri anggota '{$username}' dengan No Surat: '{$nomorSurat}'");

        return redirect()->to(base_url('admin/cooperative/resign-requests'))->with('message', 'Permohonan pengunduran diri berhasil disetujui. Surat keputusan resmi telah diterbitkan.');
    } catch (\App\Exceptions\TransactionLockException $e) {
        $msg = "Sistem sedang melayani antrean persetujuan dokumen resmi lain secara padat. Permohonan gagal diproses, silakan coba kembali beberapa saat lagi.";
        return redirect()->back()->with('error', $msg);
    } catch (\Throwable $e) {
        return redirect()->back()->with('error', 'Terjadi galat sistem: ' . $e->getMessage());
    }
    }

    /**
     * Tolak permohonan pengunduran diri (Rejected).
     */
    public function reject(int $id)
    {
        if (!auth()->user()->inGroup('admin', 'superadmin', 'manager')) {
            return redirect()->to(base_url('admin'))->with('error', 'Akses Ditolak.');
        }

        $request = $this->resignModel->find($id);
        if (!$request) {
            return redirect()->back()->with('error', 'Permohonan pengunduran diri tidak ditemukan.');
        }

        if ($request['status'] !== 'pending') {
            return redirect()->back()->with('error', 'Permohonan ini sudah diproses sebelumnya.');
        }

        $rules = [
            'alasan_penolakan' => 'required|min_length[5]|max_length[1000]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $alasanPenolakan = esc(trim($this->request->getPost('alasan_penolakan')));

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $updateStatus = $this->resignModel->update($id, [
                'status'           => 'rejected',
                'alasan_penolakan' => $alasanPenolakan,
                'processed_by'     => auth()->id(),
                'processed_at'     => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ]);

            if (!$updateStatus) {
                $db->transRollback();
                return redirect()->back()->with('error', 'Gagal memperbarui status permohonan pengunduran diri.');
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return redirect()->back()->with('error', 'Transaksi database gagal.');
            }

            $db->transCommit();

            // Kirim notifikasi inbox penolakan ke anggota
            $member = $this->anggotaModel->find($request['anggota_id']);
            $userId = $member ? $member['user_id'] : null;

            if ($userId) {
                $subject = 'Pengajuan Pengunduran Diri Ditolak';
                $message = "Yth. Anggota,<br/><br/>Kami menginformasikan bahwa permohonan pengunduran diri yang Anda ajukan pada " . date('d M Y, H:i', strtotime($request['created_at'])) . " telah <strong>DITOLAK</strong> oleh pengurus koperasi.<br/><br/><strong>Alasan Penolakan Pengurus:</strong><br/>" . nl2br((string) esc($alasanPenolakan)) . "<br/><br/>Silakan hubungi pengurus koperasi atau selesaikan kewajiban finansial Anda terlebih dahulu jika diperlukan sebelum mengajukan permohonan baru.<br/><br/>Salam,<br/>Pengurus KSP";

                try {
                    $this->messageModel->insert([
                        'user_id'             => $userId,
                        'sender_id'           => auth()->id(),
                        'invitation_id'       => null,
                        'subject'             => $subject,
                        'message'             => $message,
                        'type'                => 'general',
                        'is_read'             => 0,
                        'action_taken'        => 0,
                        'deleted_by_sender'   => 0,
                        'deleted_by_receiver' => 0,
                    ]);
                } catch (\Throwable $e) {
                    log_message('error', "Gagal mengirimkan notifikasi inbox rejected untuk user {$userId}: " . $e->getMessage());
                }

                // Audit logging
                $userObj = auth()->getProvider()->find($userId);
                $username = $userObj ? $userObj->username : 'Unknown';
                AuditLogModel::log('coop_resign_rejected', "Menolak pengunduran diri anggota '{$username}' dengan alasan: '{$alasanPenolakan}'");
            }

            return redirect()->to(base_url('admin/cooperative/resign-requests'))->with('message', 'Permohonan pengunduran diri berhasil ditolak.');

        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Terjadi galat sistem: ' . $e->getMessage());
        }
    }
}
