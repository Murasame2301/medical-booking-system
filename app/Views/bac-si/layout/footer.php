      <footer class="chantrang">
        <p>&copy; 2026 Phòng khám - Hệ thống quản lý lịch khám</p>
      </footer>
    </main>
  </div>

  <?php foreach ((array) ($jsTrang ?? []) as $tenFileJs): ?>
    <script src="assets/js/bac-si/<?= h($tenFileJs) ?>"></script>
  <?php endforeach; ?>
</body>

</html>
