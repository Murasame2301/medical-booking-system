    </main>

    <footer class="chan-trang">
        <div class="rong">
            <p><strong>Hệ thống Đặt Lịch Khám Bệnh Trực Tuyến</strong></p>
            <p>Địa chỉ: 231 đường 3/2, P. Ninh Kiều, TP. Cần Thơ</p>
            <p>&copy; <?= date('Y') ?> Cổng đặt lịch khám bệnh trực tuyến.</p>
        </div>
    </footer>

    <?php foreach ((array) ($jsTrang ?? []) as $tenFileJs): ?>
        <script src="assets/js/quan-tri/<?= h($tenFileJs) ?>?v=2"></script>
    <?php endforeach; ?>
</body>

</html>
