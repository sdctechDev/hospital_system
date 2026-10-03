<?php
// POST api/update_appointment_status.php  (appointment_id, status)
// Doctor: own appointments only. Admin: any appointment.
require_once __DIR__ . '/../includes/auth.php';

requireMethod('POST');
requireRoleApi(['doctor', 'admin']);

$in = getInput();

$doctorId = currentRole() === 'doctor' ? currentUserId() : null;

$result = (new Appointment())->updateStatus(
    (int) ($in['appointment_id'] ?? 0),
    $in['status'] ?? '',
    $doctorId
);

jsonResponse($result, $result['success'] ? 200 : 422);
