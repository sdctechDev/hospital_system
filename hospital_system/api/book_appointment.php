<?php
// POST api/book_appointment.php  (doctor_id, date, time, reason)
require_once __DIR__ . '/../includes/auth.php';

requireMethod('POST');
requireRoleApi('patient');

$in = getInput();

$result = (new Appointment())->book(
    currentUserId(),
    (int) ($in['doctor_id'] ?? 0),
    trim($in['date'] ?? ''),
    trim($in['time'] ?? ''),
    $in['reason'] ?? ''
);

jsonResponse($result, $result['success'] ? 200 : 422);
