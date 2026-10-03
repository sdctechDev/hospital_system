<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('doctor');

$doctorObj = new Doctor();
$doctorId  = currentUserId();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $result = ['success' => false, 'message' => 'Unknown action.'];

    if ($action === 'add') {
        $result = $doctorObj->addAvailability(
            $doctorId,
            $_POST['day_of_week'] ?? '',
            $_POST['start_time'] ?? '',
            $_POST['end_time'] ?? '',
            (int) ($_POST['slot_duration'] ?? 30)
        );
    } elseif ($action === 'delete') {
        $result = $doctorObj->deleteAvailability((int) ($_POST['availability_id'] ?? 0), $doctorId);
    }

    setFlash($result['success'] ? 'success' : 'error', $result['message']);
    header('Location: availability.php');
    exit;
}

$slots = $doctorObj->getAvailability($doctorId);
$days  = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

$pageTitle = 'My Availability';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">My Availability</h1>
<?php renderFlash(); ?>

<div class="card">
    <h2>Add working hours</h2>
    <form method="post" action="availability.php">
        <input type="hidden" name="action" value="add">
        <div class="row">
            <div class="form-group">
                <label>Day *</label>
                <select name="day_of_week" required>
                    <?php foreach ($days as $day): ?><option value="<?= $day ?>"><?= $day ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Slot duration (minutes) *</label>
                <select name="slot_duration">
                    <option value="15">15</option>
                    <option value="20">20</option>
                    <option value="30" selected>30</option>
                    <option value="45">45</option>
                    <option value="60">60</option>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="form-group">
                <label>Start time *</label>
                <input type="time" name="start_time" required>
            </div>
            <div class="form-group">
                <label>End time *</label>
                <input type="time" name="end_time" required>
            </div>
        </div>
        <button type="submit" class="btn">Add</button>
    </form>
</div>

<div class="card">
    <h2>Current schedule</h2>
    <div class="table-wrap">
    <table>
        <thead><tr><th>Day</th><th>Start</th><th>End</th><th>Slot</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach ($slots as $s): ?>
            <tr>
                <td><?= e($s['day_of_week']) ?></td>
                <td><?= e(substr($s['start_time'], 0, 5)) ?></td>
                <td><?= e(substr($s['end_time'], 0, 5)) ?></td>
                <td><?= (int) $s['slot_duration'] ?> min</td>
                <td>
                    <form method="post" action="availability.php" onsubmit="return confirm('Remove this schedule?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="availability_id" value="<?= (int) $s['availability_id'] ?>">
                        <button class="btn btn-sm btn-danger">Remove</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$slots): ?><tr><td colspan="5" class="muted">No schedule yet. Patients cannot book you until you add working hours.</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
