<?php
session_start();
include 'data.php';

$fotoWebsite = array(    
    'banner' => 'images/navbar.jpeg',     
    'tentang' => 'images/produk.jpeg'             
);

// Page View 
$file_counter = 'counter.txt';
if (!file_exists($file_counter)) {
    file_put_contents($file_counter, '0');
}
$jumlah_pengunjung = (int) file_get_contents($file_counter);
$jumlah_pengunjung++;
file_put_contents($file_counter, (string) $jumlah_pengunjung);

if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = array();
}

$halaman = isset($_GET['halaman']) ? $_GET['halaman'] : 'home';
$halaman_valid = array('home', 'menu', 'detail', 'keranjang', 'checkout', 'konfirmasi', 'tentang', 'kontak', 'faq');
if (!in_array($halaman, $halaman_valid)) {
    $halaman = 'home';
}

$pesan = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah'])) {
    $id_produk = isset($_POST['id_produk']) ? (int)$_POST['id_produk'] : 0;
    if (isset($produk[$id_produk])) {
        if (isset($_SESSION['keranjang'][$id_produk])) {
            $_SESSION['keranjang'][$id_produk]++;
        } else {
            $_SESSION['keranjang'][$id_produk] = 1;
        }
        header('Location: index.php?halaman=keranjang&pesan=tambah');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ubah_keranjang'])) {
    foreach ($_POST['jumlah'] as $id => $jumlah) {
        $id = (int)$id;
        $jumlah = (int)$jumlah;
        if ($jumlah <= 0) {
            unset($_SESSION['keranjang'][$id]);
        } elseif (isset($produk[$id])) {
            $_SESSION['keranjang'][$id] = $jumlah;
        }
    }
    header('Location: index.php?halaman=keranjang');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['hapus_produk'])) {
    $id_hapus = (int)$_POST['hapus_produk'];
    unset($_SESSION['keranjang'][$id_hapus]);
    header('Location: index.php?halaman=keranjang');
    exit;
}

$total_item = 0;
$subtotal = 0;
foreach ($_SESSION['keranjang'] as $id => $jumlah) {
    if (isset($produk[$id])) {
        $total_item += $jumlah;
        $subtotal += $produk[$id]['harga'] * $jumlah;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['kirim_pesanan'])) {
    $nama = trim($_POST['nama']);
    $whatsapp = trim($_POST['whatsapp']);
    $alamat = trim($_POST['alamat']);
    $pembayaran = trim($_POST['pembayaran']);
    $catatan = trim($_POST['catatan']);
    if ($nama != '' && $whatsapp != '' && $alamat != '' && $total_item > 0) {
        $_SESSION['pesanan_terakhir'] = array(
            'nama' => $nama, 'whatsapp' => $whatsapp, 'alamat' => $alamat,
            'pembayaran' => $pembayaran, 'catatan' => $catatan,
            'subtotal' => $subtotal, 'keranjang' => $_SESSION['keranjang']
        );
        $_SESSION['keranjang'] = array();
        header('Location: index.php?halaman=konfirmasi');
        exit;
    } else {
        $pesan = 'Lengkapi data pemesanan dan pastikan keranjang tidak kosong.';
    }
}

function rupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}
function aman($teks) {
    return htmlspecialchars($teks, ENT_QUOTES, 'UTF-8');
}
function jumlah_keranjang() {
    $jumlah = 0;
    if (isset($_SESSION['keranjang'])) {
        foreach ($_SESSION['keranjang'] as $isi) { $jumlah += $isi; }
    }
    return $jumlah;
}
$kata_kunci = isset($_GET['cari']) ? trim($_GET['cari']) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rissolo | Risol Lezat, Setiap Saat</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <a class="logo" href="index.php?halaman=home">Rissolo<span>.</span></a>
        <nav class="main-nav" aria-label="Main navigation">
            <a class="<?php echo $halaman == 'home' ? 'aktif' : ''; ?>" href="index.php?halaman=home">Home</a>
            <a class="<?php echo $halaman == 'tentang' ? 'aktif' : ''; ?>" href="index.php?halaman=tentang">About Us</a>
            <a class="<?php echo $halaman == 'menu' ? 'aktif' : ''; ?>" href="index.php?halaman=menu">Menu</a>
            <a class="<?php echo $halaman == 'faq' ? 'aktif' : ''; ?>" href="index.php?halaman=faq">FAQ</a>
            <a class="<?php echo $halaman == 'kontak' ? 'aktif' : ''; ?>" href="index.php?halaman=kontak">Contact</a>
        </nav>
        <a class="cart-link" href="index.php?halaman=keranjang" aria-label="Keranjang belanja">
            <svg class="cart-icon" viewBox="0 0 32 32" aria-hidden="true"><path d="M3 5h4l3.1 15.2a2 2 0 0 0 2 1.6h12.8a2 2 0 0 0 1.9-1.4L30 10H9"/><circle cx="13" cy="27" r="1.8"/><circle cx="24" cy="27" r="1.8"/></svg>
            <span><?php echo jumlah_keranjang(); ?></span>
        </a>
    </div>
</header>
<main>
<?php if ($halaman == 'home') { ?>
    <section class="hero">
        <div class="hero-copy">
            <p class="eyebrow">Renyah di luar, lembut di dalam</p>
            <h1>Rissolo</h1>
            <h2>Risol Lezat, Setiap Saat</h2>
            <p>Camilan praktis dengan rasa premium, dibuat dari bahan pilihan untuk menemani hari-harimu.</p>
            <a class="btn" href="index.php?halaman=menu">Pesan Sekarang <span>→</span></a>
        </div>

    </section>
    <section class="section wrap">
        <div class="section-heading"><div><p class="eyebrow">Pilihan favorit</p><h2>Produk Kami</h2></div><a class="text-link" href="index.php?halaman=menu">Lihat Semua →</a></div>
        <div class="product-grid">
        <?php foreach ($produk as $id => $item) { ?>
            <article class="product-card">
                <a href="index.php?halaman=detail&id=<?php echo $id; ?>"><img src="<?php echo aman($item['gambar']); ?>" alt="<?php echo aman($item['nama']); ?>"></a>
                <div class="product-info"><h3><?php echo aman($item['nama']); ?></h3><p class="price"><?php echo rupiah($item['harga']); ?></p>
                    <form method="post" action="index.php?halaman=home"><input type="hidden" name="id_produk" value="<?php echo $id; ?>"><button class="small-add" type="submit" name="tambah" aria-label="Tambah ke keranjang">+</button></form>
                </div>
            </article>
        <?php } ?>
        </div>
    </section>
    <section class="frozen-promo wrap"><div class="frozen-mark">❄</div><div class="frozen-copy"><p class="eyebrow">Stok camilan di rumah</p><h2>Rissolo Frozen, goreng kapan saja!</h2><p>Ingin camilan hangat tanpa harus pesan setiap saat? Rissolo juga tersedia dalam versi frozen. Simpan di freezer, lalu goreng saat ingin menikmati risol renyah di rumah.</p><a class="btn" href="index.php?halaman=faq#cara-goreng">Lihat Cara Menggoreng <span>→</span></a></div><div class="frozen-note"><strong>Tips Rissolo</strong><span>Goreng saat masih beku dan gunakan minyak yang cukup banyak.</span></div></section>
    <section class="home-note wrap"><p class="eyebrow">Dari Rissolo untuk kamu</p><h2>Teman kecil untuk momen yang lebih hangat.</h2><p>Temani waktu belajar, istirahat, atau kumpul bareng teman dengan pilihan risol favoritmu. Pilih varian, masukkan ke keranjang, lalu lanjutkan pemesanan dengan mudah.</p><a class="text-link" href="index.php?halaman=menu">Temukan favoritmu →</a></section>
<?php } elseif ($halaman == 'menu') { ?>
    <section class="page-banner" style="background-image: linear-gradient(120deg,rgba(36,0,1,.93),rgba(56,0,1,.62)), url('<?php echo aman($fotoWebsite['banner']); ?>');"><p class="eyebrow">Hangat, renyah, dan bikin nagih</p><h1>Menu Kami</h1><p>Pilih risol favoritmu dan nikmati kelezatannya.</p></section>
    <section class="section wrap">
        <form class="search-form" method="get" action="index.php"><input type="hidden" name="halaman" value="menu"><label class="sr-only" for="cari">Cari menu</label><input id="cari" type="text" name="cari" placeholder="Cari varian risol..." value="<?php echo aman($kata_kunci); ?>"><button class="btn" type="submit">Cari Menu</button></form>
        <?php if ($kata_kunci != '') { ?><p class="search-result">Hasil pencarian untuk: <strong><?php echo aman($kata_kunci); ?></strong></p><?php } ?>
        <div class="product-grid">
        <?php $ditemukan = false; foreach ($produk as $id => $item) { if ($kata_kunci == '' || stripos($item['nama'], $kata_kunci) !== false) { $ditemukan = true; ?>
            <article class="product-card menu-card"><a href="index.php?halaman=detail&id=<?php echo $id; ?>"><img src="<?php echo aman($item['gambar']); ?>" alt="<?php echo aman($item['nama']); ?>"></a><div class="product-info"><h3><?php echo aman($item['nama']); ?></h3><p class="price"><?php echo rupiah($item['harga']); ?></p><div class="card-actions"><a class="btn btn-light" href="index.php?halaman=detail&id=<?php echo $id; ?>">Detail</a><form method="post" action="index.php?halaman=menu"><input type="hidden" name="id_produk" value="<?php echo $id; ?>"><button class="btn" type="submit" name="tambah">Tambah</button></form></div></div></article>
        <?php } } if (!$ditemukan) { ?><p class="empty-state">Menu yang kamu cari belum tersedia. Coba kata kunci lain.</p><?php } ?>
        </div>
    </section>
<?php } elseif ($halaman == 'detail') { $id = isset($_GET['id']) ? (int)$_GET['id'] : 0; if (!isset($produk[$id])) { ?>
    <section class="section wrap"><div class="empty-state"><h2>Produk tidak ditemukan</h2><a class="btn" href="index.php?halaman=menu">Kembali ke Menu</a></div></section>
<?php } else { $item = $produk[$id]; ?>
    <section class="section wrap"><a class="back-link" href="index.php?halaman=menu">← Kembali ke Menu</a><div class="detail-layout"><div class="detail-photo"><img src="<?php echo aman($item['gambar']); ?>" alt="<?php echo aman($item['nama']); ?>"></div><div class="detail-copy"><p class="eyebrow">Rissolo pilihanmu</p><h1><?php echo aman($item['nama']); ?></h1><p class="detail-price"><?php echo rupiah($item['harga']); ?></p><p><?php echo aman($item['deskripsi']); ?></p><ul class="benefit-list"><li>✓ Bahan pilihan</li><li>✓ Dibuat dengan higienis</li><li>✓ Gurih dan lezat</li></ul><form method="post" action="index.php?halaman=detail&id=<?php echo $id; ?>"><input type="hidden" name="id_produk" value="<?php echo $id; ?>"><button class="btn" type="submit" name="tambah">＋ Tambah ke Keranjang</button></form><div class="detail-extra"><h3>Detail Produk</h3><p><span>Berat</span> ± 100 gram / pcs</p><p><span>Cocok untuk</span> Camilan, sarapan, atau teman ngopi</p></div></div></div></section>
<?php } } elseif ($halaman == 'keranjang') { ?>
    <section class="section wrap"><p class="eyebrow">Cek kembali pilihanmu</p><h1 class="page-title">🛒 Keranjang Belanja</h1><?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'tambah') { ?><p class="notice">Produk berhasil ditambahkan ke keranjang.</p><?php } ?>
    <?php if ($total_item == 0) { ?><div class="empty-state"><div class="empty-icon">🛒</div><h2>Keranjangmu masih kosong</h2><p>Yuk, pilih risol favoritmu terlebih dahulu.</p><a class="btn" href="index.php?halaman=menu">Lihat Menu</a></div><?php } else { ?>
    <div class="cart-layout"><div class="cart-box"><form method="post" action="index.php?halaman=keranjang"><table class="cart-table"><thead><tr><th>Produk</th><th>Harga</th><th>Jumlah</th><th>Total</th><th></th></tr></thead><tbody><?php foreach ($_SESSION['keranjang'] as $id => $jumlah) { if (isset($produk[$id])) { $item = $produk[$id]; ?><tr><td><div class="cart-product"><img src="<?php echo aman($item['gambar']); ?>" alt=""><span><?php echo aman($item['nama']); ?></span></div></td><td><?php echo rupiah($item['harga']); ?></td><td><input class="quantity-input" type="number" min="0" name="jumlah[<?php echo $id; ?>]" value="<?php echo $jumlah; ?>"></td><td><strong><?php echo rupiah($item['harga'] * $jumlah); ?></strong></td><td><button class="remove-button" type="submit" name="hapus_produk" value="<?php echo $id; ?>" formnovalidate>×</button></td></tr><?php } } ?></tbody></table><button class="btn btn-light" type="submit" name="ubah_keranjang">Perbarui Keranjang</button></form></div><aside class="summary-box"><h2>Ringkasan Belanja</h2><p>Total Produk <strong><?php echo $total_item; ?> pcs</strong></p><hr><p>Subtotal <strong><?php echo rupiah($subtotal); ?></strong></p><p class="total-line">Total <strong><?php echo rupiah($subtotal); ?></strong></p><a class="btn full" href="index.php?halaman=checkout">Lanjut ke Checkout →</a><a class="continue-link" href="index.php?halaman=menu">← Lanjut Belanja</a></aside></div><?php } ?></section>
<?php } elseif ($halaman == 'checkout') { ?>
    <section class="section wrap"><p class="eyebrow">Satu langkah lagi</p><h1 class="page-title">Checkout</h1><?php if ($pesan != '') { ?><p class="notice notice-error"><?php echo aman($pesan); ?></p><?php } ?><?php if ($total_item == 0) { ?><div class="empty-state"><h2>Keranjang masih kosong</h2><a class="btn" href="index.php?halaman=menu">Pilih Menu</a></div><?php } else { ?><div class="checkout-layout"><form class="form-box" method="post" action="index.php?halaman=checkout"><h2>Informasi Pelanggan</h2><label for="nama">Nama Lengkap *</label><input id="nama" name="nama" type="text" required placeholder="Masukkan nama lengkap"><label for="whatsapp">Nomor WhatsApp *</label><input id="whatsapp" name="whatsapp" type="tel" required placeholder="Contoh: 085606780305"><label for="alamat">Alamat Lengkap *</label><textarea id="alamat" name="alamat" required rows="3" placeholder="Masukkan alamat pengiriman"></textarea><label for="pembayaran">Metode Pembayaran *</label><select id="pembayaran" name="pembayaran" required><option value="Transfer Bank">Transfer Bank</option><option value="COD">Bayar di tempat (COD)</option><option value="QRIS">QRIS</option></select><label for="catatan">Catatan (opsional)</label><textarea id="catatan" name="catatan" rows="2" placeholder="Catatan untuk pesanan"></textarea><button class="btn full" type="submit" name="kirim_pesanan">Kirim Pesanan</button></form><aside class="summary-box"><h2>Ringkasan Pesanan</h2><?php foreach ($_SESSION['keranjang'] as $id => $jumlah) { if (isset($produk[$id])) { ?><div class="summary-product"><img src="<?php echo aman($produk[$id]['gambar']); ?>" alt=""><div><strong><?php echo aman($produk[$id]['nama']); ?></strong><span><?php echo $jumlah; ?> pcs</span></div><b><?php echo rupiah($produk[$id]['harga'] * $jumlah); ?></b></div><?php } } ?><hr><p>Subtotal <strong><?php echo rupiah($subtotal); ?></strong></p><p class="total-line">Total <strong><?php echo rupiah($subtotal); ?></strong></p><small>Ongkos kirim dikonfirmasi saat pemesanan.</small></aside></div><?php } ?></section>
<?php } elseif ($halaman == 'konfirmasi') { $pesanan = isset($_SESSION['pesanan_terakhir']) ? $_SESSION['pesanan_terakhir'] : null; ?>
    <section class="confirmation wrap"><div class="success-mark">✓</div><p class="eyebrow">Terima kasih sudah memilih Rissolo</p><h1>Pesanan Berhasil Dicatat!</h1><p>Data pesananmu sudah tersimpan pada sesi website ini. Silakan hubungi kami melalui WhatsApp untuk konfirmasi pesanan.</p><?php if ($pesanan) { ?><div class="confirmation-summary"><p>Nama <strong><?php echo aman($pesanan['nama']); ?></strong></p><p>Nomor WhatsApp <strong><?php echo aman($pesanan['whatsapp']); ?></strong></p><p>Metode pembayaran <strong><?php echo aman($pesanan['pembayaran']); ?></strong></p><p>Total <strong><?php echo rupiah($pesanan['subtotal']); ?></strong></p></div><?php } ?><a class="btn" href="https://wa.me/6285606780305" target="_blank" rel="noopener">Hubungi WhatsApp</a><p><a class="text-link" href="index.php?halaman=home">Kembali ke Beranda</a></p></section>
<?php } elseif ($halaman == 'tentang') { ?>
    <section class="page-banner" style="background-image: linear-gradient(120deg,rgba(36,0,1,.93),rgba(56,0,1,.62)), url('<?php echo aman($fotoWebsite['banner']); ?>');">
        <p class="eyebrow">Cerita kecil yang terus berkembang</p>
        <h1>Tentang Rissolo</h1>
        <p>Berawal dari open PO sederhana, sekarang hadir dengan lebih banyak pilihan untuk menemani waktu ngemilmu.</p>
    </section>

    <section class="about-section wrap">
        <div class="about-photo">
            <img src="<?php echo aman($fotoWebsite['tentang']); ?>" alt="Risol Rissolo">
        </div>
        <div class="about-story">
            <p class="eyebrow">Dari dapur kecil, untuk banyak cerita</p>
            <h2>Berawal dari satu rasa, tumbuh jadi banyak pilihan.</h2>
            <p>Rissolo mulai dirintis pada tahun 2025 oleh Denia, seorang remaja asal Kediri yang ingin mencoba membangun usaha camilan dari langkah sederhana. Awalnya, Rissolo hanya menerima open PO melalui WhatsApp dan menawarkan satu menu yaitu risol mayo.</p>
            <p>Dari pesanan yang datang satu per satu, Rissolo terus belajar dan berkembang. Pilihan rasa pun bertambah mulai dari cokelat, rogout hingga ayam suwir ori dan hot. Kini, pelanggan juga bisa memilih Rissolo dalam kemasan Frozen untuk disimpan di freezer dan digoreng saat ingin menikmati camilan hangat di rumah.</p>
            <p>Bagi Rissolo, perjalanan ini bukan tentang langsung menjadi besar, tetapi tentang berani memulai, mendengarkan kritik dan saran pelanggan serta terus memperbaiki produk sedikit demi sedikit.</p>
            <a class="btn" href="index.php?halaman=menu">Lihat Varian Rissolo <span>→</span></a>
        </div>
    </section>

    <section class="journey-section wrap">
        <div class="journey-heading">
            <h2>Perjalanan Rissolo</h2>
            <p>Setiap tahap dimulai dari langkah kecil dan semangat untuk mencoba.</p>
        </div>
        <div class="journey-grid">
            <article class="journey-card">
                <span class="journey-year">2025</span>
                <h3>Mulai dari Open PO</h3>
                <p>Rissolo memulai penjualan melalui WhatsApp dengan satu pilihan rasa, yaitu risol mayo.</p>
            </article>
            <article class="journey-card">
                <span class="journey-year">Bertumbuh</span>
                <h3>Beragam Menu</h3>
                <p>Menu berkembang dengan pilihan manis, gurih dan pedas agar pelanggan punya lebih banyak varian untuk dipilih.</p>
            </article>
            <article class="journey-card">
                <span class="journey-year">Sekarang</span>
                <h3>Hadir dalam Versi Frozen</h3>
                <p>Rissolo Frozen memberi pilihan yang lebih praktis, simpan dalam freezer dan goreng saat ingin menikmatinya.</p>
            </article>
        </div>
    </section>
<?php } elseif ($halaman == 'kontak') { ?>
    <section class="page-banner" style="background-image: linear-gradient(120deg,rgba(36,0,1,.93),rgba(56,0,1,.62)), url('<?php echo aman($fotoWebsite['banner']); ?>');"><p class="eyebrow">Kami senang mendengar notifikasi darimu</p><h1>Kontak Kami</h1><p>Ada pertanyaan tentang varian, pemesanan, atau Rissolo Frozen? Kami siap membantu.</p></section><section class="contact-grid wrap"><div class="contact-card"><span class="contact-icon">☏</span><h2>WhatsApp</h2><p>0856 0678 0305</p><a class="text-link" href="https://wa.me/6285606780305" target="_blank" rel="noopener">Hubungi kami →</a></div><div class="contact-card"><span class="contact-icon">◎</span><h2>Instagram</h2><p>@rissolo.o</p><a class="text-link" href="https://www.instagram.com/rissolo.o/?mdxt=MTdsY2Fsd2Fvbmh3cQ==" target="_blank" rel="noopener">Kunjungi Instagram →</a></div><div class="contact-card"><span class="contact-icon">⌖</span><h2>Lokasi</h2><p>Surabaya, Jawa Timur</p><span class="muted">Informasi lokasi pengambilan dapat dikonfirmasi melalui WhatsApp.</span></div></section>
<?php } elseif ($halaman == 'faq') { ?>
    <section class="page-banner faq-banner" style="background-image: linear-gradient(120deg,rgba(36,0,1,.94),rgba(56,0,1,.76)), url('<?php echo aman($fotoWebsite['banner']); ?>');"><p class="eyebrow">Biar makin mudah menikmati Rissolo</p><h1>Frequently Asked Questions</h1><p>Jawaban singkat untuk pertanyaan yang sering ditanyakan tentang produk, penyimpanan, dan pemesanan.</p></section>
    <section class="faq-section wrap"><div class="faq-intro"><p class="eyebrow">Punya pertanyaan?</p><h2>Yuk, cari tahu dulu!</h2><p>Mulai dari cara menggoreng Rissolo Frozen sampai cara memesan, semoga informasi ini membantu kamu menikmati Rissolo dengan lebih praktis.</p></div>
        <div class="faq-group"><h3 class="faq-category">❄ Rissolo Frozen</h3>
            <details class="faq-item" id="cara-goreng" open><summary>Bagaimana cara menggoreng Rissolo Frozen?</summary><div class="faq-answer"><p>Goreng Rissolo dalam kondisi masih beku, jadi tidak perlu dicairkan terlebih dahulu. Panaskan minyak yang cukup banyak hingga risol bisa terendam. Gunakan api kecil, masukkan risol perlahan dan goreng sampai kulitnya berwarna keemasan serta matang merata. Hindari memasukkan terlalu banyak risol sekaligus agar suhu minyak tidak turun drastis.</p><
            /div></details>
            <details class="faq-item"><summary>Apakah Rissolo Frozen harus dicairkan dulu?</summary><div class="faq-answer"><p>Tidak perlu. Untuk cara menggoreng yang praktis, masukkan Rissolo saat masih beku ke dalam minyak yang sudah panas. Hati-hati karena minyak dapat memercik saat menggoreng makanan beku.</p></div></details>
            <details class="faq-item"><summary>Bagaimana cara menyimpan Rissolo Frozen?</summary><div class="faq-answer"><p>Simpan dalam freezer dengan kemasan tertutup rapat. Hindari membiarkan risol terlalu lama di suhu ruang. Setelah mencair, sebaiknya segera diolah dan jangan dibekukan ulang.</p></div></details>
            <details class="faq-item"><summary>Berapa lama Rissolo Frozen bisa disimpan?</summary><div class="faq-answer"><p>Lama penyimpanan dapat bergantung pada kondisi produk, kemasan, dan suhu freezer. Untuk informasi tanggal produksi, batas konsumsi, serta petunjuk penyimpanan khusus, tanyakan langsung kepada tim Rissolo saat memesan.</p></div></details>
        </div>
        <div class="faq-group"><h3 class="faq-category">♡ Produk & Pemesanan</h3>
            <details class="faq-item"><summary>Apa saja varian Rissolo?</summary><div class="faq-answer"><p>Saat ini terdapat Rissolo Coklat, Mayo, Ayam Suwir Ori, Ayam Suwir Hot, dan Rogout. Ketersediaan varian bisa dikonfirmasi melalui WhatsApp atau Instagram kami.</p></div></details>
            <details class="faq-item"><summary>Berapa harga Rissolo?</summary><div class="faq-answer"><p>Harga Rissolo reguler yang tercantum di website adalah Rp4.000 per buah. Untuk harga dan paket Rissolo Frozen, silakan tanyakan langsung kepada admin sebelum memesan.</p></div></details>
            <details class="faq-item"><summary>Bagaimana cara memesan Rissolo?</summary><div class="faq-answer"><p>Pilih varian di halaman Menu, tambahkan ke keranjang, lalu isi data checkout. Setelah itu, hubungi WhatsApp Rissolo untuk mengonfirmasi detail pesanan, ketersediaan produk, dan pengiriman atau pengambilan.</p></div></details>
            <details class="faq-item"><summary>Apakah bisa bertanya tentang stok dan pengiriman?</summary><div class="faq-answer"><p>Bisa! Hubungi kami melalui WhatsApp di 0856 0678 0305 untuk mengecek stok, pilihan Rissolo Frozen, biaya pengiriman, serta lokasi pengambilan.</p></div></details>
        </div>
        <div class="faq-contact"><div><p class="eyebrow">Belum menemukan jawaban?</p><h2>Tanya langsung ke Rissolo</h2><p>Kami bantu cek informasi produk dan pesananmu.</p></div><a class="btn" href="https://wa.me/6285606780305?text=Halo%20Rissolo%2C%20saya%20ingin%20bertanya%20tentang%20produk." target="_blank" rel="noopener">Chat WhatsApp ↗</a></div>
    </section>
<?php } ?>
</main>



<footer class="site-footer">
    <div class="footer-grid">
        <div class="footer-brand">
            <a class="logo logo-light" href="index.php?halaman=home">Rissolo<span>.</span></a>
            <p>Risol Lezat, Setiap Saat</p>
            <p>Nikmati camilan lezat dan praktis bersama Rissolo. Temukan rasa favoritmu dan jadikan setiap momen lebih istimewa.</p>
            <div class="social-links">
                <a href="https://www.instagram.com/rissolo.o/?mdxt=MTdsY2Fsd2Fvbmh3cQ==" target="_blank" rel="noopener" aria-label="Instagram Rissolo">◎ Instagram @rissolo.o</a>
            </div>
        </div>

        <div class="footer-column">
            <h3>Our Menu</h3>
            <a href="index.php?halaman=menu">All Flavors</a>
            <a href="index.php?halaman=menu&amp;cari=Coklat">Rissolo Coklat</a>
            <a href="index.php?halaman=menu&amp;cari=Mayo">Rissolo Mayo</a>
            <a href="index.php?halaman=menu&amp;cari=Ayam">Rissolo Ayam Suwir</a>
        </div>

        <div class="footer-column">
            <h3>Quick Links</h3>
            <a href="index.php?halaman=home">Home</a>
            <a href="index.php?halaman=tentang">About Us</a>
            <a href="index.php?halaman=menu">Menu</a>
            <a href="index.php?halaman=faq">FAQ</a>
            <a href="index.php?halaman=kontak">Contact</a>
            <a href="index.php?halaman=keranjang">Shopping Cart</a>
        </div>

        <div class="footer-column">
            <h3>Get in Touch</h3>
            <p>☏ 0856 0678 0305</p>
            <p>⌖ Surabaya, East Java</p>
            <a href="https://wa.me/6285606780305" target="_blank" rel="noopener">Chat with us on WhatsApp ↗</a>
            <a href="https://www.instagram.com/rissolo.o/?mdxt=MTdsY2Fsd2Fvbmh3cQ==" target="_blank" rel="noopener">Follow us on Instagram ↗</a>
        </div>
    </div>

    <div class="footer-bottom">
        <span>© 2026 Rissolo. All rights reserved.</span>
        <span>Page views: <strong><?php echo number_format($jumlah_pengunjung, 0, ",", "."); ?></strong></span>
        <span>Made with ♡ for risol lovers.</span>
    </div>
</footer>
</body>
</html>
