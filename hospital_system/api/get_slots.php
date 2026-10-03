<?php
// GET api/get_slots.php?doctor_id=1&date=2026-10-10
require_once __DIR__ . '/../includes/auth.php';

requireMethod('GET');
requireRoleApi('patient');

$doctorId = (int) ($_GET['doctor_id'] ?? 0);
$date     = $_GET['date'] ?? '';

if (!$doctorId || $date === '') {
    jsonResponse(['success' => false, 'message' => 'doctor_id and date are required.'], 400);
}

$d = DateTime::createFromFormat('Y-m-d', $date);
if (!$d || $d->format('Y-m-d') !== $date) {
    jsonResponse(['success' => false, 'message' => 'Invalid date format.'], 400);
}
if ($date < date('Y-m-d')) {
    jsonResponse(['success' => false, 'message' => 'You cannot book a past date.'], 400);
}

$doctorObj = new Doctor();
$doctor = $doctorObj->getById($doctorId);
if (!$doctor || $doctor['status'] !== 'active') {
    jsonResponse(['success' => false, 'message' => 'Doctor not found.'], 404);
}

$slots = $doctorObj->getAvailableSlots($doctorId, $date);

jsonResponse([
    'success' => true,
    'doctor'  => $doctor['full_name'],
    'date'    => $date,
    'fee'     => (float) $doctor['consultation_fee'],
    'slots'   => $slots,
    'message' => $slots ? count($slots) . ' slot(s) available.' : 'No available slots for this date.',
]);
