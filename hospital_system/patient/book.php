<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('patient');

$doctors  = (new Doctor())->getAll();
$selected = (int) ($_GET['doctor_id'] ?? 0);

$pageTitle = 'Book Appointment';
$extraScripts = ['ui.js', 'book.js'];
require __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Book an Appointment</h1>
<div id="js-message"></div>

<div class="card" style="max-width:640px">
    <div class="form-group">
        <label for="doctor_id">Doctor *</label>
        <select id="doctor_id">
            <option value="">Select a doctor</option>
            <?php foreach ($doctors as $d): ?>
                <option value="<?= (int) $d['doctor_id'] ?>" data-fee="<?= e($d['consultation_fee']) ?>"
                    <?= $selected === (int) $d['doctor_id'] ? 'selected' : '' ?>>
                    Dr. <?= e($d['full_name']) ?> - <?= e($d['specialization']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="muted mt" id="fee"></p>
    </div>

    <div class="form-group">
        <label for="date">Date *</label>
        <input type="date" id="date" min="<?= date('Y-m-d') ?>">
    </div>

    <div class="form-group">
        <label>Available times *</label>
        <div id="slots" class="slots"><span class="muted">Choose a doctor and date.</span></div>
    </div>

    <div class="form-group">
        <label for="reason">Reason for visit</label>
        <textarea id="reason" rows="3" maxlength="500"></textarea>
    </div>

    <button type="button" id="book-btn" class="btn">Book and continue to payment</button>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
