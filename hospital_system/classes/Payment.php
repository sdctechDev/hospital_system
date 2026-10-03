<?php
class Payment
{
    private Database $db;

    // Test card numbers (simulation only, no real money)
    private const TEST_CARDS = [
        '4242424242424242' => ['success' => true,  'message' => 'Payment successful.'],
        '4000000000000002' => ['success' => false, 'message' => 'Card declined.'],
        '4000000000009995' => ['success' => false, 'message' => 'Insufficient funds.'],
        '4000000000000069' => ['success' => false, 'message' => 'Card expired.'],
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Simulate a payment for an appointment
    public function process(int $appointmentId, int $patientId, string $method, array $details): array
    {
        if (!in_array($method, ['card', 'bank_transfer'], true)) {
            return ['success' => false, 'message' => 'Invalid payment method.'];
        }

        $appt = $this->db->fetchOne(
            "SELECT appointment_id, fee, status FROM appointments
             WHERE appointment_id = ? AND patient_id = ?",
            [$appointmentId, $patientId]
        );
        if (!$appt) {
            return ['success' => false, 'message' => 'Appointment not found.'];
        }
        if ($appt['status'] !== 'pending') {
            return ['success' => false, 'message' => 'This appointment is not awaiting payment.'];
        }
        if ((new Appointment())->isPaid($appointmentId)) {
            return ['success' => false, 'message' => 'This appointment is already paid.'];
        }

        // Format validation (not recorded, the user just fixes the form)
        $error = $method === 'card' ? $this->validateCard($details) : $this->validateBank($details);
        if ($error) {
            return ['success' => false, 'message' => $error];
        }

        // Simulated gateway decision
        $result = $method === 'card'
            ? $this->simulateCard($details)
            : $this->simulateBank($details);

        $amount = (float) $appt['fee'];
        $ref    = 'TXN' . strtoupper(bin2hex(random_bytes(6)));
        $status = $result['success'] ? 'success' : 'failed';

        $pdo = $this->db->getConnection();
        try {
            $pdo->beginTransaction();

            $this->db->execute(
                "INSERT INTO payments (appointment_id, amount, payment_method, transaction_ref, status)
                 VALUES (?, ?, ?, ?, ?)",
                [$appointmentId, $amount, $method, $ref, $status]
            );
            $paymentId = (int) $this->db->lastInsertId();

            if ($result['success']) {
                $this->db->execute(
                    "UPDATE appointments SET status = 'confirmed' WHERE appointment_id = ?",
                    [$appointmentId]
                );
            }

            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['success' => false, 'message' => 'Payment could not be saved. Try again.'];
        }

        return [
            'success'         => $result['success'],
            'message'         => $result['message'],
            'payment_id'      => $paymentId,
            'transaction_ref' => $ref,
            'amount'          => $amount,
        ];
    }

    // Refund a paid appointment (simulated). Returns the refund reference, or null if nothing was paid.
    public function refundForAppointment(int $appointmentId): ?string
    {
        $pay = $this->db->fetchOne(
            "SELECT payment_id FROM payments WHERE appointment_id = ? AND status = 'success' LIMIT 1",
            [$appointmentId]
        );
        if (!$pay) {
            return null;
        }

        $ref = 'RFD' . strtoupper(bin2hex(random_bytes(6)));
        $this->db->execute(
            "UPDATE payments SET status = 'refunded', refund_ref = ?, refunded_at = NOW() WHERE payment_id = ?",
            [$ref, $pay['payment_id']]
        );
        return $ref;
    }

    // ---------- VALIDATION ----------

    private function validateCard(array $d): ?string
    {
        $number = preg_replace('/\s+/', '', $d['card_number'] ?? '');
        $expiry = trim($d['expiry'] ?? '');
        $cvv    = trim($d['cvv'] ?? '');
        $name   = trim($d['card_name'] ?? '');

        if ($name === '') {
            return 'Cardholder name is required.';
        }
        if (!preg_match('/^\d{16}$/', $number)) {
            return 'Card number must be 16 digits.';
        }
        if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $expiry)) {
            return 'Expiry must be in MM/YY format.';
        }
        [$mm, $yy] = explode('/', $expiry);
        $expiresAt = strtotime("last day of 20$yy-$mm 23:59:59");
        if ($expiresAt < time()) {
            return 'Card has expired.';
        }
        if (!preg_match('/^\d{3}$/', $cvv)) {
            return 'CVV must be 3 digits.';
        }
        return null;
    }

    private function validateBank(array $d): ?string
    {
        if (!preg_match('/^\d{10}$/', trim($d['account_number'] ?? ''))) {
            return 'Account number must be 10 digits.';
        }
        if (trim($d['account_name'] ?? '') === '') {
            return 'Account name is required.';
        }
        return null;
    }

    // ---------- SIMULATION ----------

    private function simulateCard(array $d): array
    {
        $number = preg_replace('/\s+/', '', $d['card_number']);
        // Known test cards use fixed outcomes, any other valid card succeeds
        return self::TEST_CARDS[$number] ?? ['success' => true, 'message' => 'Payment successful.'];
    }

    private function simulateBank(array $d): array
    {
        // Account 0000000000 always fails, others succeed
        if (trim($d['account_number']) === '0000000000') {
            return ['success' => false, 'message' => 'Bank transfer failed. Account not found.'];
        }
        return ['success' => true, 'message' => 'Payment successful.'];
    }

    // ---------- READ ----------

    // Receipt for a patient's own payment
    public function getReceipt(int $paymentId, int $patientId): ?array
    {
        return $this->db->fetchOne(
            "SELECT pay.*, a.appointment_date, a.appointment_time, a.reason,
                    p.full_name AS patient_name, p.email AS patient_email,
                    d.full_name AS doctor_name, s.name AS specialization
             FROM payments pay
             JOIN appointments a ON a.appointment_id = pay.appointment_id
             JOIN patients p ON p.patient_id = a.patient_id
             JOIN doctors d ON d.doctor_id = a.doctor_id
             JOIN specializations s ON s.specialization_id = d.specialization_id
             WHERE pay.payment_id = ? AND a.patient_id = ?",
            [$paymentId, $patientId]
        );
    }

    public function getByPatient(int $patientId): array
    {
        return $this->db->fetchAll(
            "SELECT pay.*, a.appointment_date, a.appointment_time, d.full_name AS doctor_name
             FROM payments pay
             JOIN appointments a ON a.appointment_id = pay.appointment_id
             JOIN doctors d ON d.doctor_id = a.doctor_id
             WHERE a.patient_id = ?
             ORDER BY pay.paid_at DESC",
            [$patientId]
        );
    }

    // Admin: all payments, optional status filter
    public function getAll(?string $status = null): array
    {
        $sql = "SELECT pay.*, p.full_name AS patient_name, d.full_name AS doctor_name
                FROM payments pay
                JOIN appointments a ON a.appointment_id = pay.appointment_id
                JOIN patients p ON p.patient_id = a.patient_id
                JOIN doctors d ON d.doctor_id = a.doctor_id";
        $params = [];

        if ($status) {
            $sql .= " WHERE pay.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY pay.paid_at DESC";
        return $this->db->fetchAll($sql, $params);
    }

    public function totalRevenue(): float
    {
        $row = $this->db->fetchOne("SELECT COALESCE(SUM(amount), 0) AS total FROM payments WHERE status = 'success'");
        return (float) $row['total'];
    }
}
