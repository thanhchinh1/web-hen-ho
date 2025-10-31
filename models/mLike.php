<?php
require_once 'mDbconnect.php';

class LikeModel {
    private $conn;

    public function __construct() {
        $db = new clsConnect();
        $this->conn = $db->connect();
    }

    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }

    /**
     * Kiểm tra xem người dùng hiện tại đã thích người kia chưa
     */
    public function isLiked(int $likerId, int $likedId): bool {
        $stmt = $this->conn->prepare('SELECT 1 FROM Thich WHERE maNguoiThich = ? AND maNguoiDuocThich = ?');
        $stmt->bind_param('ii', $likerId, $likedId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    /**
     * Thêm hoặc xóa lượt thích giữa hai người dùng.
     * Trả về true nếu sau khi thực hiện người dùng ở trạng thái "đã thích".
     */
    public function toggleLike(int $likerId, int $likedId): bool {
        if ($this->isLiked($likerId, $likedId)) {
            $stmt = $this->conn->prepare('DELETE FROM Thich WHERE maNguoiThich = ? AND maNguoiDuocThich = ?');
            $stmt->bind_param('ii', $likerId, $likedId);
            $stmt->execute();
            return false;
        }

        $stmt = $this->conn->prepare('INSERT INTO Thich (maNguoiThich, maNguoiDuocThich) VALUES (?, ?)');
        $stmt->bind_param('ii', $likerId, $likedId);

        if (!$stmt->execute()) {
            throw new \Exception('Unable to like user');
        }

        return true;
    }

    /**
     * Lấy danh sách ID người dùng mà $userId đã thích.
     */
    public function getLikedUserIds(int $userId): array {
        $stmt = $this->conn->prepare('SELECT maNguoiDuocThich FROM Thich WHERE maNguoiThich = ?');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $ids = [];
        while ($row = $result->fetch_assoc()) {
            $ids[] = (int) $row['maNguoiDuocThich'];
        }

        return $ids;
    }

    /**
     * Lấy danh sách hồ sơ mà người dùng đã thích.
     */
    public function getUsersLikedBy(int $userId): array {
        $stmt = $this->conn->prepare('
            SELECT h.*, t.thoiDiemThich
            FROM Thich t
            INNER JOIN HoSo h ON h.maNguoiDung = t.maNguoiDuocThich
            WHERE t.maNguoiThich = ?
            ORDER BY t.thoiDiemThich DESC
        ');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }

        return $users;
    }

    /**
     * Lấy danh sách hồ sơ của những người đã thích người dùng hiện tại.
     */
    public function getUsersWhoLiked(int $userId): array {
        $stmt = $this->conn->prepare('
            SELECT h.*, t.thoiDiemThich
            FROM Thich t
            INNER JOIN HoSo h ON h.maNguoiDung = t.maNguoiThich
            WHERE t.maNguoiDuocThich = ?
            ORDER BY t.thoiDiemThich DESC
        ');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }

        return $users;
    }
}
