<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <div>
        <div class="footer-brand">
          <span class="logo-badge" style="width:42px;height:42px"><img src="<?= BASE_URL ?>/assets/logo.png" alt="<?= h($T['brand']['name']) ?>"></span>
          <span class="brand-name" style="font-size:19px"><?= h($T['brand']['name']) ?></span>
        </div>
        <p class="footer-desc"><?= h($T['footer']['desc']) ?></p>
      </div>
      <div>
        <div class="footer-heading"><?= h($T['footer']['quick']) ?></div>
        <div class="footer-link-list">
          <a href="<?= BASE_URL ?>/index.php" class="footer-link"><?= h($T['nav']['home']) ?></a>
          <a href="<?= BASE_URL ?>/courses.php" class="footer-link"><?= h($T['nav']['courses']) ?></a>
          <a href="<?= BASE_URL ?>/halls.php" class="footer-link"><?= h($T['nav']['halls']) ?></a>
          <a href="<?= BASE_URL ?>/portal.php" class="footer-link"><?= h($T['nav']['myPage']) ?></a>
        </div>
      </div>
      <div>
        <div class="footer-heading"><?= h($T['footer']['contact']) ?></div>
        <div class="footer-link-list">
          <?php
          if (!function_exists('load_branches')) { require_once __DIR__ . '/functions.php'; }
          $footerBranches = load_branches();
          foreach ($footerBranches as $fb):
          ?>
            <div style="margin-bottom:10px">
              <div class="footer-link" style="cursor:default;font-weight:700;color:var(--text)"><?= h($fb['name'][$lang]) ?></div>
              <span class="footer-link" style="cursor:default"><span data-lucide="map-pin" style="width:13px;height:13px;margin-inline-end:6px"></span><?= h($fb['address'][$lang]) ?></span>
              <span class="footer-link" style="cursor:default" dir="ltr"><span data-lucide="phone" style="width:13px;height:13px;margin-inline-end:6px"></span><?= h($fb['phone']) ?></span>
              <?php if (!empty($fb['email'])): ?>
              <span class="footer-link" style="cursor:default" dir="ltr"><span data-lucide="mail" style="width:13px;height:13px;margin-inline-end:6px"></span><?= h($fb['email']) ?></span>
              <?php endif; ?>
              <?php if (!empty($fb['hours'][$lang])): ?>
              <span class="footer-link" style="cursor:default"><span data-lucide="clock" style="width:13px;height:13px;margin-inline-end:6px"></span><?= h($fb['hours'][$lang]) ?></span>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Social Media Links -->
    <div class="footer-social-bar">
      <span class="footer-social-label"><?= $lang === 'ar' ? 'تواصل معنا' : 'Follow us' ?></span>
      <div class="footer-social-icons">
        <!-- WhatsApp -->
        <a href="https://wa.me/201002732802" target="_blank" rel="noopener noreferrer" class="social-icon-btn whatsapp-btn" aria-label="WhatsApp" title="WhatsApp: 01002732802">
          <svg viewBox="0 0 24 24" fill="currentColor" width="22" height="22">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
          </svg>
        </a>
        <!-- Instagram -->
        <a href="https://www.instagram.com/lctcompany?igsh=Mmd1ZXp2OHBnYm91" target="_blank" rel="noopener noreferrer" class="social-icon-btn instagram-btn" aria-label="Instagram" title="Instagram: @lctcompany">
          <svg viewBox="0 0 24 24" fill="currentColor" width="22" height="22">
            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
          </svg>
        </a>
        <!-- Facebook -->
        <a href="https://www.facebook.com/share/197dbzY8ao/?mibextid=wwXIfr" target="_blank" rel="noopener noreferrer" class="social-icon-btn facebook-btn" aria-label="Facebook" title="Facebook: LCT Academy">
          <svg viewBox="0 0 24 24" fill="currentColor" width="22" height="22">
            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
          </svg>
        </a>
      </div>
    </div>

    <div class="footer-bottom">
      <span>© <?= date('Y') ?> <?= h($T['brand']['name']) ?> — <?= h($T['footer']['rights']) ?></span>
      <span><?= h($T['brand']['tag']) ?></span>
    </div>
  </div>
</footer>
<!-- Floating WhatsApp Button -->
<a href="https://wa.me/201002732802" target="_blank" rel="noopener noreferrer" class="wa-float-btn" aria-label="تواصل عبر واتساب">
  <svg viewBox="0 0 24 24" fill="currentColor" width="28" height="28">
    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
  </svg>
</a>
</body>
</html>


