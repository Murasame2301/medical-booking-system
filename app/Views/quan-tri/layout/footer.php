    </main>

    <footer class="chan-trang">
        <div class="rong">
            <p><strong>Hệ thống Đặt Lịch Khám Bệnh Trực Tuyến</strong></p>
            <p>Địa chỉ: 123 Đường 456, TP. Cần Thơ</p>
            <p>&copy; <?= date('Y') ?> Đồ án môn CT428</p>
        </div>
    </footer>

    <?php foreach ((array) ($jsTrang ?? []) as $tenFileJs): ?>
        <script src="assets/js/quan-tri/<?= h($tenFileJs) ?>"></script>
    <?php endforeach; ?>
</body>

</html>
