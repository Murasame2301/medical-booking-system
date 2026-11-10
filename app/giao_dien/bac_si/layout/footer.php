      <footer class="chantrang">
        <p>&copy; 2026 Phòng khám - Hệ thống quản lý lịch khám</p>
      </footer>
    </main>
  </div>
<!-- tao script ung voi trang -->
<!-- cac bien lay tu khoi_tao.php -->
<?php foreach ((array) ($js_trang_lay_duoc ?? []) as $ten_file_js): ?>
    <script src="cong_khai/js/<?= h($ten_file_js) ?>"></script>
<?php endforeach; ?>
</body>
 
</html>