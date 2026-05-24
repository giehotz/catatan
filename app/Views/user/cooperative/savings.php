<?= $this->extend('layouts/koprasi_base') ?>

<?= $this->section('koprasi_content') ?>
<div class="space-y-8 max-w-6xl mx-auto py-4 mt-4">
    
    <!-- Navigation Back links -->
    <div class="flex items-center justify-between">
        <a href="<?= base_url('cooperative') ?>" class="inline-flex items-center gap-1.5 text-xs font-bold text-tx-secondary hover:text-tx-primary transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Dasbor Koperasi Saya
        </a>
        <span class="text-xs text-tx-disabled font-semibold font-mono">Savings Desk</span>
    </div>

    <!-- Title Header -->
    <div class="space-y-1">
        <h1 class="text-3xl font-extrabold text-tx-primary tracking-tight">Simpanan & Mutasi Saya</h1>
        <p class="text-tx-secondary text-sm">Kelola setoran simpanan wajib, pokok, sukarela, dan ajukan penarikan dana sukarela secara mandiri.</p>
    </div>

    <!-- Message Banners -->
    <?php if (session('message') !== null) : ?>
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <?= session('message') ?>
        </div>
    <?php endif ?>

    <?php if (session('error') !== null) : ?>
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <?= session('error') ?>
        </div>
    <?php endif ?>

    <?php if (isset($errors)) : ?>
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs space-y-1">
            <strong class="font-bold text-tx-primary block mb-1">Gagal mengajukan transaksi:</strong>
            <?php foreach ($errors as $error) : ?>
                <p>• <?= (string) esc($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        
        <!-- Forms Column -->
        <div class="space-y-6 md:col-span-1">
            
            <!-- Bank Account Info Card -->
            <div class="bg-surface/40 border border-br-default rounded-2xl p-5 space-y-4">
                <h4 class="text-sm font-bold text-tx-primary uppercase tracking-wider">Rekening Tujuan Transfer</h4>
                <p class="text-xs text-tx-disabled leading-relaxed">Silakan transfer setoran simpanan Anda ke rekening resmi koperasi berikut ini:</p>
                <div class="space-y-3 text-xs">
                    <?php if (!empty($bankAccounts)): ?>
                        <?php foreach ($bankAccounts as $bank): ?>
                            <div class="p-3 bg-base/60 border border-br-default/60 rounded-xl space-y-0.5">
                                <span class="text-tx-secondary block font-semibold"><?= esc($bank['nama']) ?></span>
                                <strong class="text-indigo-400 block font-mono select-all text-sm"><?= esc($bank['nomor']) ?></strong>
                                <?php if (!empty($bank['atas_nama'])): ?>
                                    <span class="text-[10px] text-tx-disabled block">a/n <?= esc($bank['atas_nama']) ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-4 bg-amber-500/5 border border-amber-500/10 rounded-xl text-amber-400 text-xs font-semibold flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            Rekening tujuan belum dikonfigurasi oleh pengurus. Hubungi admin koperasi.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Deposit Form -->
            <div class="bg-surface/40 border border-br-default rounded-2xl p-5 space-y-4">
                <h4 class="text-sm font-bold text-tx-primary uppercase tracking-wider">Setor Simpanan</h4>
                
                <form action="<?= base_url('cooperative/deposit') ?>" method="post" enctype="multipart/form-data" class="space-y-4">
                    <?= csrf_field() ?>

                    <div class="space-y-1">
                        <label for="jenis_simpanan" class="text-xs font-bold text-tx-secondary uppercase tracking-wider block">Jenis Simpanan</label>
                        <select id="jenis_simpanan" name="jenis_simpanan" required class="w-full px-3 py-2 bg-base/60 border border-br-default rounded-lg text-tx-primary text-xs font-bold outline-none focus:border-indigo-500 cursor-pointer">
                            <option value="pokok">Simpanan Pokok (Awal Masuk)</option>
                            <option value="wajib">Simpanan Wajib (Bulanan)</option>
                            <option value="sukarela">Simpanan Sukarela (Bebas)</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label for="nominal" class="text-xs font-bold text-tx-secondary uppercase tracking-wider block">Nominal Setoran</label>
                        <input type="number" id="nominal" name="nominal" required min="1" placeholder="Contoh: 100000" class="w-full px-3 py-2 bg-base/60 border border-br-default rounded-lg focus:border-indigo-500 text-tx-primary placeholder-tx-disabled transition-all outline-none text-xs font-semibold">
                    </div>

                    <div class="space-y-1">
                        <label for="bukti_transfer" class="text-xs font-bold text-tx-secondary uppercase tracking-wider block">Bukti Transfer (Gambar)</label>
                        <input type="file" id="bukti_transfer" name="bukti_transfer" required accept="image/*" class="w-full px-2 py-1.5 bg-base/60 border border-br-default rounded-lg text-xs text-tx-secondary outline-none file:mr-2.5 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-[10px] file:font-bold file:bg-elevated file:text-indigo-400 hover:file:bg-elevated cursor-pointer">
                    </div>

                    <div class="space-y-1">
                        <label for="keterangan" class="text-xs font-bold text-tx-secondary uppercase tracking-wider block">Keterangan Tambahan</label>
                        <input type="text" id="keterangan" name="keterangan" placeholder="Contoh: Setoran Wajib Mei 2026" class="w-full px-3 py-2 bg-base/60 border border-br-default rounded-lg focus:border-indigo-500 text-tx-primary placeholder-tx-disabled transition-all outline-none text-xs font-semibold">
                    </div>

                    <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-500 text-tx-primary font-bold rounded-lg text-xs transition-colors cursor-pointer shadow-md shadow-emerald-600/10">
                        Kirim Bukti Setoran
                    </button>
                </form>
            </div>

            <!-- Withdraw Sukarela Form -->
            <div class="bg-surface/40 border border-br-default rounded-2xl p-5 space-y-4">
                <h4 class="text-sm font-bold text-tx-primary uppercase tracking-wider">Tarik Simpanan Sukarela</h4>
                <p class="text-[10px] text-tx-disabled leading-relaxed">Penarikan hanya dapat diajukan dari akumulasi Simpanan Sukarela Anda yang telah disetujui.</p>

                <form action="<?= base_url('cooperative/withdraw') ?>" method="post" class="space-y-4">
                    <?= csrf_field() ?>

                    <div class="space-y-1">
                        <label for="nominal_tarik" class="text-xs font-bold text-tx-secondary uppercase tracking-wider block">Nominal Penarikan</label>
                        <input type="number" id="nominal_tarik" name="nominal" required min="1" placeholder="Masukkan jumlah penarikan" class="w-full px-3 py-2 bg-base/60 border border-br-default rounded-lg focus:border-indigo-500 text-tx-primary placeholder-tx-disabled transition-all outline-none text-xs font-semibold">
                    </div>

                    <div class="space-y-1">
                        <label for="keterangan_tarik" class="text-xs font-bold text-tx-secondary uppercase tracking-wider block">Nomor Rekening Anda (Untuk Transfer)</label>
                        <input type="text" id="keterangan_tarik" name="keterangan" required placeholder="Contoh: BCA 1234567 a/n Nama Saya" class="w-full px-3 py-2 bg-base/60 border border-br-default rounded-lg focus:border-indigo-500 text-tx-primary placeholder-tx-disabled transition-all outline-none text-xs font-semibold">
                    </div>

                    <button type="submit" class="w-full py-2 bg-amber-600 hover:bg-amber-500 text-tx-primary font-bold rounded-lg text-xs transition-colors cursor-pointer shadow-md shadow-amber-600/10">
                        Ajukan Penarikan
                    </button>
                </form>
            </div>

        </div>

        <!-- Ledger Table Column -->
        <div class="bg-surface/40 border border-br-default rounded-2xl shadow-xl overflow-hidden md:col-span-2 h-fit">
            <div class="p-6 border-b border-br-default/60 flex items-center justify-between">
                <h3 class="text-lg font-bold text-tx-primary tracking-tight">Riwayat Pengajuan Simpanan Saya</h3>
                <span class="px-2.5 py-0.5 bg-base/80 text-[10px] font-bold rounded-lg text-tx-secondary border border-br-default uppercase">
                    Ledger
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-br-default text-xs font-bold text-tx-secondary uppercase tracking-wider bg-base/40">
                            <th class="py-4 px-6">Tanggal Transaksi</th>
                            <th class="py-4 px-6 text-center">Jenis Simpanan</th>
                            <th class="py-4 px-6 text-center">Tipe Aksi</th>
                            <th class="py-4 px-6 text-right">Nominal</th>
                            <th class="py-4 px-6 text-center">Status</th>
                            <th class="py-4 px-6">Catatan Transfer</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-br-default/60 text-sm text-slate-300">
                        <?php if (empty($savingsList)) : ?>
                            <tr>
                                <td colspan="6" class="py-8 text-center text-tx-disabled font-semibold">Belum ada riwayat transaksi simpanan.</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($savingsList as $s) : ?>
                                <tr class="hover:bg-base/30 transition-colors">
                                    <td class="py-4 px-6 text-xs text-tx-secondary font-semibold font-mono">
                                        <?= date('d M Y, H:i', strtotime($s['tanggal_transaksi'])) ?>
                                    </td>
                                    <td class="py-4 px-6 text-center font-bold text-indigo-300 uppercase text-xs">
                                        <?= (string) esc($s['jenis_simpanan']) ?>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <?php if ($s['tipe_transaksi'] === 'setoran') : ?>
                                            <span class="px-2 py-0.5 text-[9px] font-bold rounded-md bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 uppercase tracking-wider">Setoran</span>
                                        <?php else : ?>
                                            <span class="px-2 py-0.5 text-[9px] font-bold rounded-md bg-amber-500/10 text-amber-400 border border-amber-500/20 uppercase tracking-wider">Penarikan</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-6 text-right font-extrabold text-tx-primary">
                                        Rp <?= number_format($s['nominal'], 2, ',', '.') ?>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <?php if ($s['status'] === 'pending') : ?>
                                            <span class="px-2 py-0.5 text-[9px] font-bold rounded-full bg-slate-500/10 text-tx-secondary border border-br-default uppercase">Pending</span>
                                        <?php elseif ($s['status'] === 'approved') : ?>
                                            <span class="px-2 py-0.5 text-[9px] font-bold rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 uppercase">Disetujui</span>
                                        <?php else : ?>
                                            <span class="px-2 py-0.5 text-[9px] font-bold rounded-full bg-rose-500/10 text-rose-400 border border-rose-500/20 uppercase">Ditolak</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-6 text-xs text-tx-secondary max-w-xs truncate" title="<?= (string) esc($s['keterangan']) ?>">
                                        <?= (string) esc($s['keterangan']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
<?= $this->endSection() ?>
