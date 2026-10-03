<?php
// POST api/cancel_appointment.php  (appointment_id)
require_once __DIR__ . '/../includes/auth.php';

requireMethod('POST');
requireRoleApi('patient');

$in = getInput();

$result = (new Appointment())->cancelByPatient(
    (int) ($in['appointment_id'] ?? 0),
    currentUserId()
);

jsonResponse($result, $result['success'] ? 200 : 422);
