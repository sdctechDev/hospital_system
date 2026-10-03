</main>
<?php foreach (($extraScripts ?? []) as $script): ?>
    <script src="<?= BASE_URL ?>assets/js/<?= e($script) ?>"></script>
<?php endforeach; ?>
</body>
</html>
