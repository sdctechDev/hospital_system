<?php
class Appointment
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ---------- BOOKING ----------

    public function book(int $patientId, int $doctorId, string $date, string $time, string $reason = ''): array
    {
        $doctorObj = new Doctor();
        $doctor = $doctorObj->getById($doctorId);

        if (!$doctor || $doctor['status'] !== 'active') {
            return ['success' => false, 'message' => 'Doctor not available.'];
        }

        // Slot must still be free (checks day, hours, past times, existing bookings)
        $time = substr($time, 0, 5); // HH:MM
        if (!in_array($time, $doctorObj->getAvailableSlots($doctorId, $date), true)) {
            return ['success' => false, 'message' => 'That time slot is no longer available.'];
        }

        $fee = (float) $doctor['consultation_fee'];

        try {
            $this->db->execute(
                "INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason, fee)
                 VALUES (?, ?, ?, ?, ?, ?)",
                [$patientId, $doctorId, $date, $time . ':00', trim($reason) ?: null, $fee]
            );
        } catch (PDOException $e) {
            // 23000 = duplicate slot (someone booked it a moment ago)
            if ($e->getCode() === '23000') {
                return ['success' => false, 'message' => 'Sorry, that slot was just taken. Pick another time.'];
            }
            throw $e;
        }

        return [
            'success'        => true,
            'message'        => 'Appointment booked. Proceed to payment.',
            'appointment_id' => (int) $this->db->lastInsertId(),
            'fee'            => $fee,
        ];
    }

    // ---------- READ ----------

    public function getById(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT a.*, p.full_name AS patient_name, p.email AS patient_email,
                    d.full_name AS doctor_name, s.name AS specialization
             FROM appointments a
             JOIN patients p ON p.patient_id = a.patient_id
             JOIN doctors d ON d.doctor_id = a.doctor_id
             JOIN specializations s ON s.specialization_id = d.specialization_id
             WHERE a.appointment_id = ?",
            [$id]
        );
    }

    public function getByPatient(int $patientId): array
    {
        return $this->db->fetchAll(
            "SELECT a.*, d.full_name AS doctor_name, s.name AS specialization,
                    (SELECT COUNT(*) FROM payments pay
                     WHERE pay.appointment_id = a.appointment_id AND pay.status = 'success') AS is_paid
             FROM appointments a
             JOIN doctors d ON d.doctor_id = a.doctor_id
             JOIN specializations s ON s.specialization_id = d.specialization_id
             WHERE a.patient_id = ?
             ORDER BY a.appointment_date DESC, a.appointment_time DESC",
            [$patientId]
        );
    }

    // $date optional (Y-m-d) to filter, e.g. today's list
    public function getByDoctor(int $doctorId, ?string $date = null): array
    {
        $sql = "SELECT a.*, p.full_name AS patient_name, p.phone AS patient_phone,
                       (SELECT COUNT(*) FROM payments pay
                        WHERE pay.appointment_id = a.appointment_id AND pay.status = 'success') AS is_paid
                FROM appointments a
                JOIN patients p ON p.patient_id = a.patient_id
                WHERE a.doctor_id = ?";
        $params = [$doctorId];

        if ($date) {
            $sql .= " AND a.appointment_date = ?";
            $params[] = $date;
        }

        $sql .= " ORDER BY a.appointment_date, a.appointment_time";
        return $this->db->fetchAll($sql, $params);
    }

    // Admin: all appointments, optional status filter
    public function getAll(?string $status = null): array
    {
        $sql = "SELECT a.*, p.full_name AS patient_name, d.full_name AS doctor_name
                FROM appointments a
                JOIN patients p ON p.patient_id = a.patient_id
                JOIN doctors d ON d.doctor_id = a.doctor_id";
        $params = [];

        if ($status) {
            $sql .= " WHERE a.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";
        return $this->db->fetchAll($sql, $params);
    }

    // ---------- STATUS ----------

    // Patient cancels their own pending/confirmed appointment
    public function cancelByPatient(int $appointmentId, int $patientId): array
    {
        $appt = $this->db->fetchOne(
            "SELECT status FROM appointments WHERE appointment_id = ? AND patient_id = ?",
            [$appointmentId, $patientId]
        );

        if (!$appt) {
            return ['success' => false, 'message' => 'Appointment not found.'];
        }
        if (!in_array($appt['status'], ['pending', 'confirmed'], true)) {
            return ['success' => false, 'message' => 'This appointment cannot be cancelled.'];
        }

        $refundRef = $this->cancelWithRefund($appointmentId);
        return [
            'success' => true,
            'message' => $refundRef
                ? 'Appointment cancelled. Refund issued (Ref: ' . $refundRef . ').'
                : 'Appointment cancelled.',
        ];
    }

    // Doctor: complete or cancel their own appointment. Admin: pass $doctorId = null
    public function updateStatus(int $appointmentId, string $newStatus, ?int $doctorId = null): array
    {
        if (!in_array($newStatus, ['confirmed', 'completed', 'cancelled'], true)) {
            return ['success' => false, 'message' => 'Invalid status.'];
        }

        $sql = "SELECT status FROM appointments WHERE appointment_id = ?";
        $params = [$appointmentId];
        if ($doctorId !== null) {
            $sql .= " AND doctor_id = ?";
            $params[] = $doctorId;
        }

        $appt = $this->db->fetchOne($sql, $params);
        if (!$appt) {
            return ['success' => false, 'message' => 'Appointment not found.'];
        }

        $current = $appt['status'];
        $allowed = [
            'pending'   => ['confirmed', 'cancelled'],
            'confirmed' => ['completed', 'cancelled'],
        ];
        if (!isset($allowed[$current]) || !in_array($newStatus, $allowed[$current], true)) {
            return ['success' => false, 'message' => "Cannot change status from $current to $newStatus."];
        }
        if ($newStatus === 'confirmed' && !$this->isPaid($appointmentId)) {
            return ['success' => false, 'message' => 'Cannot confirm an unpaid appointment.'];
        }

        if ($newStatus === 'cancelled') {
            $refundRef = $this->cancelWithRefund($appointmentId);
            return [
                'success' => true,
                'message' => $refundRef
                    ? 'Appointment cancelled. Patient refunded (Ref: ' . $refundRef . ').'
                    : 'Appointment cancelled.',
            ];
        }

        $this->db->execute(
            "UPDATE appointments SET status = ? WHERE appointment_id = ?",
            [$newStatus, $appointmentId]
        );
        return ['success' => true, 'message' => 'Status updated to ' . $newStatus . '.'];
    }

    // Cancel and refund (if paid) together, so both happen or neither does
    private function cancelWithRefund(int $appointmentId): ?string
    {
        $pdo = $this->db->getConnection();
        try {
            $pdo->beginTransaction();
            $this->db->execute(
                "UPDATE appointments SET status = 'cancelled' WHERE appointment_id = ?",
                [$appointmentId]
            );
            $refundRef = (new Payment())->refundForAppointment($appointmentId);
            $pdo->commit();
            return $refundRef;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public function isPaid(int $appointmentId): bool
    {
        $row = $this->db->fetchOne(
            "SELECT payment_id FROM payments WHERE appointment_id = ? AND status = 'success' LIMIT 1",
            [$appointmentId]
        );
        return $row !== null;
    }

    // ---------- STATS (dashboards) ----------

    public function countByStatus(?int $doctorId = null): array
    {
        $sql = "SELECT status, COUNT(*) AS total FROM appointments";
        $params = [];
        if ($doctorId !== null) {
            $sql .= " WHERE doctor_id = ?";
            $params[] = $doctorId;
        }
        $sql .= " GROUP BY status";

        $counts = ['pending' => 0, 'confirmed' => 0, 'completed' => 0, 'cancelled' => 0];
        foreach ($this->db->fetchAll($sql, $params) as $r) {
            $counts[$r['status']] = (int) $r['total'];
        }
        return $counts;
    }
}
