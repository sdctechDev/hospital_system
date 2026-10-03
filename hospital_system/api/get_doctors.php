<?php
// GET api/get_doctors.php            (all active doctors)
// GET api/get_doctors.php?specialization_id=2
require_once __DIR__ . '/../includes/auth.php';

requireMethod('GET');
requireRoleApi('patient');

$specId = (int) ($_GET['specialization_id'] ?? 0);

$doctorObj = new Doctor();

jsonResponse([
    'success'         => true,
    'doctors'         => $doctorObj->getAll($specId ?: null),
    'specializations' => $doctorObj->getSpecializations(),
]);
