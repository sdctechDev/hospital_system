<?php
class Patient
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function emailExists(string $email): bool
    {
        $row = $this->db->fetchOne(
            "SELECT patient_id FROM patients WHERE email = ?",
            [$email]
        );
        return $row !== null;
    }

    // Register a new patient. Returns ['success' => bool, 'message' => string]
    public function register(array $data): array
    {
        $name   = trim($data['full_name'] ?? '');
        $email  = strtolower(trim($data['email'] ?? ''));
        $pass   = $data['password'] ?? '';
        $phone  = trim($data['phone'] ?? '');
        $gender = $data['gender'] ?? null;
        $dob    = $data['date_of_birth'] ?? null;
        $addr   = trim($data['address'] ?? '');

        if ($name === '' || $email === '' || $pass === '') {
            return ['success' => false, 'message' => 'Name, email and password are required.'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email address.'];
        }
        if (strlen($pass) < 6) {
            return ['success' => false, 'message' => 'Password must be at least 6 characters.'];
        }
        if ($gender !== null && !in_array($gender, ['male', 'female', 'other'], true)) {
            return ['success' => false, 'message' => 'Invalid gender.'];
        }
        if ($this->emailExists($email)) {
            return ['success' => false, 'message' => 'Email already registered.'];
        }

        $this->db->execute(
            "INSERT INTO patients (full_name, email, password, phone, gender, date_of_birth, address)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $name,
                $email,
                password_hash($pass, PASSWORD_DEFAULT),
                $phone ?: null,
                $gender ?: null,
                $dob ?: null,
                $addr ?: null,
            ]
        );

        return ['success' => true, 'message' => 'Registration successful. You can now log in.'];
    }

    // Verify credentials and start the session
    public function login(string $email, string $password): array
    {
        $email = strtolower(trim($email));
        $user  = $this->db->fetchOne("SELECT * FROM patients WHERE email = ?", [$email]);

        if (!$user || !password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }

        session_regenerate_id(true);
        $_SESSION['user_id']   = (int) $user['patient_id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['role']      = 'patient';
        $_SESSION['last_activity'] = time();

        return ['success' => true, 'message' => 'Login successful.'];
    }

    public function getById(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT patient_id, full_name, email, phone, gender, date_of_birth, address, created_at
             FROM patients WHERE patient_id = ?",
            [$id]
        );
    }

    public function updateProfile(int $id, array $data): array
    {
        $name = trim($data['full_name'] ?? '');
        if ($name === '') {
            return ['success' => false, 'message' => 'Name is required.'];
        }

        $this->db->execute(
            "UPDATE patients SET full_name = ?, phone = ?, address = ? WHERE patient_id = ?",
            [$name, trim($data['phone'] ?? '') ?: null, trim($data['address'] ?? '') ?: null, $id]
        );
        $_SESSION['user_name'] = $name;

        return ['success' => true, 'message' => 'Profile updated.'];
    }
}