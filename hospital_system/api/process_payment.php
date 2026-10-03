<?php
// POST api/process_payment.php
// card:  appointment_id, payment_method=card, card_name, card_number, expiry, cvv
// bank:  appointment_id, payment_method=bank_transfer, account_name, account_number
require_once __DIR__ . '/../includes/auth.php';

requireMethod('POST');
requireRoleApi('patient');

$in = getInput();

$result = (new Payment())->process(
    (int) ($in['appointment_id'] ?? 0),
    currentUserId(),
    $in['payment_method'] ?? '',
    $in
);

jsonResponse($result, $result['success'] ? 200 : 422);
