<?php
class Admin
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
        $user  = $this->db->fetchOne("SELECT * FROM admins WHERE email = ?", [$email]);

        if (!$user || !password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }

        session_regenerate_id(true);
        $_SESSION['user_id']   = (int) $user['admin_id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['role']      = 'admin';
        $_SESSION['last_activity'] = time();

        return ['success' => true, 'message' => 'Login successful.'];
    }

    // ---------- DASHBOARD ----------

    public function getStats(): array
    {
        $one = fn(string $sql) => (int) $this->db->fetchOne($sql)['c'];

        $payment = new Payment();

        return [
            'patients'        => $one("SELECT COUNT(*) AS c FROM patients"),
            'doctors'         => $one("SELECT COUNT(*) AS c FROM doctors"),
            'appointments'    => $one("SELECT COUNT(*) AS c FROM appointments"),
            'failed_payments' => $one("SELECT COUNT(*) AS c FROM payments WHERE status = 'failed'"),
            'revenue'         => $payment->totalRevenue(),
            'by_status'       => (new Appointment())->countByStatus(),
        ];
    }

    // ---------- PATIENTS ----------

    public function getAllPatients(): array
    {
        return $this->db->fetchAll(
            "SELECT patient_id, full_name, email, phone, gender, date_of_birth, created_at
             FROM patients ORDER BY created_at DESC"
        );
    }

    // ---------- DOCTORS (list incl. inactive) ----------

    public function getAllDoctors(): array
    {
        return $this->db->fetchAll(
            "SELECT d.doctor_id, d.full_name, d.email, d.phone, d.consultation_fee, d.status,
                    s.name AS specialization
             FROM doctors d
             JOIN specializations s ON s.specialization_id = d.specialization_id
             ORDER BY d.full_name"
        );
    }

    // ---------- SPECIALIZATIONS ----------

    public function addSpecialization(string $name, string $description = ''): array
    {
        $name = trim($name);
        if ($name === '') {
            return ['success' => false, 'message' => 'Name is required.'];
        }
        if ($this->db->fetchOne("SELECT specialization_id FROM specializations WHERE name = ?", [$name])) {
            return ['success' => false, 'message' => 'Specialization already exists.'];
        }

        $this->db->execute(
            "INSERT INTO specializations (name, description) VALUES (?, ?)",
            [$name, trim($description) ?: null]
        );
        return ['success' => true, 'message' => 'Specialization added.'];
    }

    public function updateSpecialization(int $id, string $name, string $description = ''): array
    {
        $name = trim($name);
        if ($name === '') {
            return ['success' => false, 'message' => 'Name is required.'];
        }
        $dup = $this->db->fetchOne(
            "SELECT specialization_id FROM specializations WHERE name = ? AND specialization_id <> ?",
            [$name, $id]
        );
        if ($dup) {
            return ['success' => false, 'message' => 'Another specialization has that name.'];
        }

        $this->db->execute(
            "UPDATE specializations SET name = ?, description = ? WHERE specialization_id = ?",
            [$name, trim($description) ?: null, $id]
        );
        return ['success' => true, 'message' => 'Specialization updated.'];
    }

    public function deleteSpecialization(int $id): array
    {
        $used = $this->db->fetchOne(
            "SELECT doctor_id FROM doctors WHERE specialization_id = ? LIMIT 1",
            [$id]
        );
        if ($used) {
            return ['success' => false, 'message' => 'Cannot delete: doctors are using this specialization.'];
        }

        $this->db->execute("DELETE FROM specializations WHERE specialization_id = ?", [$id]);
        return ['success' => true, 'message' => 'Specialization deleted.'];
    }
}