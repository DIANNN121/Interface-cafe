<?php
require_once 'config.php';

$sql = "SELECT * FROM menu WHERE is_active = 1";
$result = $conn->query($sql);
$menu_items = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $menu_items[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kafe Nimet - Authentic Coffee Experience</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container nav-container">
            <a href="index.php" class="logo">Kafe Nimet</a>
            <div class="nav-links">
                <a href="#home">Home</a>
                <a href="#menu">Menu</a>
                <a href="#about">About</a>
                <a href="Login.php" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Staff Login</a>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section id="home" class="hero">
        <div class="hero-content">
            <h1>Rasakan Kehangatan <br>di Setiap Tegukan</h1>
            <p>Kopi pilihan terbaik dengan suasana 'Nimet' yang menenangkan.</p>
            <a href="pelanggan/dashboard.php" class="btn btn-primary">Pesan Sekarang</a>
        </div>
    </section>

    <!-- Menu Section -->
    <section id="menu" class="section-padding menu-section">
        <div class="container">
            <div class="text-center">
                <h2 class="section-title">Menu Favorit Kami</h2>
                <div class="filter-buttons">
                    <button class="filter-btn active" onclick="filterMenu('all')">Semua</button>
                    <button class="filter-btn" onclick="filterMenu('Minuman')">Minuman</button>
                    <button class="filter-btn" onclick="filterMenu('Makanan')">Makanan</button>
                    <button class="filter-btn" onclick="filterMenu('Camilan')">Camilan</button>
                </div>
            </div>

            <div class="menu-grid">
                <?php foreach ($menu_items as $item): ?>
                    <div class="menu-card" data-category="<?= htmlspecialchars($item['category']) ?>">
                        <div class="menu-image">
                            <?php if (!empty($item['image']) && file_exists($item['image'])): ?>
                                <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <i class="fas fa-coffee fa-3x" style="color: #ccc;"></i>
                            <?php endif; ?>
                        </div>
                        <div class="menu-details">
                            <div class="menu-header">
                                <h3 class="menu-title"><?= htmlspecialchars($item['name']) ?></h3>
                                <span class="menu-price">Rp <?= number_format($item['price'], 0, ',', '.') ?></span>
                            </div>
                            <p class="menu-desc"><?= htmlspecialchars($item['description']) ?></p>
                            <a href="pelanggan/dashboard.php" class="btn btn-outline" style="width: 100%; font-size: 0.9rem;">Pesan Sekarang</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="section-padding" style="background-color: #fff;">
        <div class="container">
            <div class="text-center">
                <h2 class="section-title">Tentang Kami</h2>
            </div>
            <div style="display: flex; gap: 4rem; align-items: center; margin-top: 2rem; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 300px;">
                    <img src="https://images.unsplash.com/photo-1554118811-1e0d58224f24?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Interior Kafe" style="width: 100%; border-radius: 12px; box-shadow: var(--shadow);">
                </div>
                <div style="flex: 1; min-width: 300px;">
                    <h3>Cerita Di Balik Secangkir Kopi</h3>
                    <p style="margin-bottom: 1rem; color: #666;">
                        Kafe Nimet lahir dari kerinduan akan suasana rumah yang hangat dan menenangkan.
                        Kami percaya bahwa kopi bukan sekadar minuman, melainkan sebuah pengalaman yang
                        dapat menyatukan berbagai cerita.
                    </p>
                    <p style="margin-bottom: 1.5rem; color: #666;">
                        Setiap biji kopi yang kami sajikan dipilih langsung dari petani lokal terbaik,
                        disangrai dengan hati-hati, dan diseduh dengan penuh cinta oleh barista kami
                        yang berpengalaman.
                    </p>
                    <div style="display: flex; gap: 2rem;">
                        <div>
                            <h4 style="font-size: 2rem; color: var(--primary-color); margin-bottom: 0;">5+</h4>
                            <p>Tahun Berjalan</p>
                        </div>
                        <div>
                            <h4 style="font-size: 2rem; color: var(--primary-color); margin-bottom: 0;">100+</h4>
                            <p>Menu Varian</p>
                        </div>
                        <div>
                            <h4 style="font-size: 2rem; color: var(--primary-color); margin-bottom: 0;">15k+</h4>
                            <p>Pelanggan Bahagia</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <h2>Kafe Nimet</h2>
                <p>Jl. Pertanian III No.22, RT.5/RW.14, Ps. Minggu, Kota Jakarta Selatan, Daerah Khusus Ibukota Jakarta 12520</p>
                <div style="margin-top: 1rem;">
                    <i class="fab fa-instagram" style="font-size: 1.5rem; margin: 0 10px;"></i>
                    <i class="fab fa-facebook" style="font-size: 1.5rem; margin: 0 10px;"></i>
                    <i class="fab fa-whatsapp" style="font-size: 1.5rem; margin: 0 10px;"></i>
                </div>
            </div>
            <div class="copyright">
                &copy; <?= date('Y') ?> Kafe Nimet. All Rights Reserved.
            </div>
        </div>
    </footer>

    <script>
        function filterMenu(category) {
            const cards = document.querySelectorAll('.menu-card');
            const btns = document.querySelectorAll('.filter-btn');

            // Update active button
            btns.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');

            cards.forEach(card => {
                if (category === 'all' || card.dataset.category === category) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(255, 255, 255, 0.98)';
                navbar.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
            } else {
                navbar.style.background = 'rgba(255, 255, 255, 0.9)';
                navbar.style.boxShadow = 'none';
            }
        });
    </script>
</body>

</html>