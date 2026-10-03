<?php
class Doctor
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ---------- AUTH ----------

    public function login(string $email, string $password): array
    {
        $email = strtolower(trim($email));
        $user  = $this->db->fetchOne("SELECT * FROM doctors WHERE email = ?", [$email]);

        if (!$user || !password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }
        if ($user['status'] !== 'active') {
            return ['success' => false, 'message' => 'This account is inactive. Contact the administrator.'];
        }

        session_regenerate_id(true);
        $_SESSION['user_id']   = (int) $user['doctor_id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['role']      = 'doctor';
        $_SESSION['last_activity'] = time();

        return ['success' => true, 'message' => 'Login successful.'];
    }

    // ---------- READ ----------

    // All active doctors, optionally filtered by specialization
    public function getAll(?int $specializationId = null): array
    {
        $sql = "SELECT d.doctor_id, d.full_name, d.email, d.phone, d.consultation_fee, d.bio, d.status,
                       s.specialization_id, s.name AS specialization
                FROM doctors d
                JOIN specializations s ON s.specialization_id = d.specialization_id
                WHERE d.status = 'active'";
        $params = [];

        if ($specializationId) {
            $sql .= " AND d.specialization_id = ?";
            $params[] = $specializationId;
        }

        $sql .= " ORDER BY d.full_name";
        return $this->db->fetchAll($sql, $params);
    }

    public function getById(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT d.doctor_id, d.full_name, d.email, d.phone, d.consultation_fee, d.bio, d.status,
                    s.specialization_id, s.name AS specialization
             FROM doctors d
             JOIN specializations s ON s.specialization_id = d.specialization_id
             WHERE d.doctor_id = ?",
            [$id]
        );
    }

    public function getSpecializations(): array
    {
        return $this->db->fetchAll("SELECT * FROM specializations ORDER BY name");
    }

    // Consultation fee for a doctor
    public function getFee(int $doctorId): ?float
    {
        $row = $this->db->fetchOne(
            "SELECT consultation_fee FROM doctors WHERE doctor_id = ?",
            [$doctorId]
        );
        return $row ? (float) $row['consultation_fee'] : null;
    }

    // ---------- AVAILABILITY ----------

    public function getAvailability(int $doctorId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM doctor_availability WHERE doctor_id = ?
             ORDER BY FIELD(day_of_week,'Mon','Tue','Wed','Thu','Fri','Sat','Sun'), start_time",
            [$doctorId]
        );
    }

    public function addAvailability(int $doctorId, string $day, string $start, string $end, int $duration = 30): array
    {
        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        if (!in_array($day, $days, true)) {
            return ['success' => false, 'message' => 'Invalid day.'];
        }
        if ($start >= $end) {
            return ['success' => false, 'message' => 'End time must be after start time.'];
        }
        if ($duration < 10 || $duration > 120) {
            return ['success' => false, 'message' => 'Slot duration must be 10 to 120 minutes.'];
        }

        $this->db->execute(
            "INSERT INTO doctor_availability (doctor_id, day_of_week, start_time, end_time, slot_duration)
             VALUES (?, ?, ?, ?, ?)",
            [$doctorId, $day, $start, $end, $duration]
        );
        return ['success' => true, 'message' => 'Availability added.'];
    }

    public function deleteAvailability(int $availabilityId, int $doctorId): array
    {
        $rows = $this->db->execute(
            "DELETE FROM doctor_availability WHERE availability_id = ? AND doctor_id = ?",
            [$availabilityId, $doctorId]
        );
        return $rows
            ? ['success' => true, 'message' => 'Availability removed.']
            : ['success' => false, 'message' => 'Availability not found.'];
    }

    // CORE: free time slots for a doctor on a date (used by the Fetch API)
    public function getAvailableSlots(int $doctorId, string $date): array
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        if (!$d || $d->format('Y-m-d') !== $date) {
            return [];
        }
        if ($date < date('Y-m-d')) {
            return []; // no past dates
        }

        $dayName = $d->format('D'); // Mon, Tue, ...

        $windows = $this->db->fetchAll(
            "SELECT start_time, end_time, slot_duration
             FROM doctor_availability
             WHERE doctor_id = ? AND day_of_week = ?",
            [$doctorId, $dayName]
        );
        if (!$windows) {
            return [];
        }

        // Times already booked (cancelled ones are free again)
        $booked = $this->db->fetchAll(
            "SELECT TIME_FORMAT(appointment_time, '%H:%i') AS t
             FROM appointments
             WHERE doctor_id = ? AND appointment_date = ? AND status <> 'cancelled'",
            [$doctorId, $date]
        );
        $bookedTimes = array_column($booked, 't');

        $isToday = ($date === date('Y-m-d'));
        $nowTime = date('H:i');
        $slots   = [];

        foreach ($windows as $w) {
            $cur  = strtotime($w['start_time']);
            $end  = strtotime($w['end_time']);
            $step = (int) $w['slot_duration'] * 60;

            while ($cur + $step <= $end) {
                $t = date('H:i', $cur);
                if (!in_array($t, $bookedTimes, true) && (!$isToday || $t > $nowTime)) {
                    $slots[] = $t;
                }
                $cur += $step;
            }
        }

        sort($slots);
        return array_values(array_unique($slots));
    }

    // ---------- ADMIN MANAGEMENT ----------

    public function create(array $data): array
    {
        $name  = trim($data['full_name'] ?? '');
        $email = strtolower(trim($data['email'] ?? ''));
        $pass  = $data['password'] ?? '';
        $specId = (int) ($data['specialization_id'] ?? 0);
        $fee   = $data['consultation_fee'] ?? '';

        if ($name === '' || $email === '' || $pass === '' || !$specId || $fee === '') {
            return ['success' => false, 'message' => 'All required fields must be filled.'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email address.'];
        }
        if (strlen($pass) < 6) {
            return ['success' => false, 'message' => 'Password must be at least 6 characters.'];
        }
        if (!is_numeric($fee) || (float) $fee < 0) {
            return ['success' => false, 'message' => 'Invalid consultation fee.'];
        }
        if ($this->db->fetchOne("SELECT doctor_id FROM doctors WHERE email = ?", [$email])) {
            return ['success' => false, 'message' => 'Email already registered.'];
        }

        $this->db->execute(
            "INSERT INTO doctors (full_name, email, password, phone, specialization_id, consultation_fee, bio)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $name,
                $email,
                password_hash($pass, PASSWORD_DEFAULT),
                trim($data['phone'] ?? '') ?: null,
                $specId,
                (float) $fee,
                trim($data['bio'] ?? '') ?: null,
            ]
        );
        return ['success' => true, 'message' => 'Doctor added.'];
    }

    public function update(int $id, array $data): array
    {
        $name   = trim($data['full_name'] ?? '');
        $email  = strtolower(trim($data['email'] ?? ''));
        $specId = (int) ($data['specialization_id'] ?? 0);
        $fee    = $data['consultation_fee'] ?? '';

        if ($name === '' || !$specId || !is_numeric($fee) || (float) $fee < 0) {
            return ['success' => false, 'message' => 'Invalid input.'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email address.'];
        }
        $dup = $this->db->fetchOne(
            "SELECT doctor_id FROM doctors WHERE email = ? AND doctor_id <> ?",
            [$email, $id]
        );
        if ($dup) {
            return ['success' => false, 'message' => 'Another doctor already uses that email.'];
        }

        $this->db->execute(
            "UPDATE doctors SET full_name = ?, email = ?, phone = ?, specialization_id = ?, consultation_fee = ?, bio = ?
             WHERE doctor_id = ?",
            [
                $name,
                $email,
                trim($data['phone'] ?? '') ?: null,
                $specId,
                (float) $fee,
                trim($data['bio'] ?? '') ?: null,
                $id,
            ]
        );
        return ['success' => true, 'message' => 'Doctor updated.'];
    }

    public function setStatus(int $id, string $status): array
    {
        if (!in_array($status, ['active', 'inactive'], true)) {
            return ['success' => false, 'message' => 'Invalid status.'];
        }
        $this->db->execute("UPDATE doctors SET status = ? WHERE doctor_id = ?", [$status, $id]);
        return ['success' => true, 'message' => 'Status updated.'];
    }
}
