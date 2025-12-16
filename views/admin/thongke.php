<?php
require_once '../../models/mSession.php';
require_once '../../models/mDbconnect.php';

Session::start();

if (!Session::get('is_admin') && Session::get('user_role') !== 'admin') {
    Session::destroy();
    header('Location: ../dangnhap/login.php');
    exit;
}

Session::set('admin_last_activity', time());
$adminId = Session::get('admin_id');
$adminName = Session::get('admin_name');

$db = clsConnect::getInstance()->connect();

// Thống kê tổng quan
$stats = [];

// Tổng người dùng
$result = $db->query("SELECT COUNT(*) as total FROM nguoidung WHERE role = 'user'");
$stats['totalUsers'] = $result->fetch_assoc()['total'];

// Người dùng VIP
$result = $db->query("SELECT COUNT(*) as total FROM goidangky WHERE loaiGoi = 'VIP' AND trangThaiGoi = 'Active'");
$stats['vipUsers'] = $result->fetch_assoc()['total'];

// Ghép đôi thành công
$result = $db->query("SELECT COUNT(*) as total FROM ghepdoi WHERE trangThaiGhepDoi = 'matched'");
$stats['totalMatches'] = $result->fetch_assoc()['total'];

// Tin nhắn
$result = $db->query("SELECT COUNT(*) as total FROM tinnhan");
$stats['totalMessages'] = $result->fetch_assoc()['total'];

// Báo cáo
$result = $db->query("SELECT COUNT(*) as total FROM baocao");
$stats['totalReports'] = $result->fetch_assoc()['total'];

// Thống kê theo ngày (7 ngày gần đây)
$result = $db->query("
    SELECT DATE(lanHoatDongCuoi) as ngay, COUNT(*) as soLuong
    FROM nguoidung
    WHERE lanHoatDongCuoi >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(lanHoatDongCuoi)
    ORDER BY ngay DESC
");
$dailyUsers = $result->fetch_all(MYSQLI_ASSOC);

// Thống kê ghép đôi theo ngày
$result = $db->query("
    SELECT DATE(thoiDiemGhepDoi) as ngay, COUNT(*) as soLuong
    FROM ghepdoi
    WHERE thoiDiemGhepDoi >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(thoiDiemGhepDoi)
    ORDER BY ngay DESC
");
$dailyMatches = $result->fetch_all(MYSQLI_ASSOC);

// Thống kê theo tháng
$result = $db->query("
    SELECT 
        MONTH(thoiDiemTao) as thang,
        COUNT(*) as soNguoiDung
    FROM goidangky
    WHERE YEAR(thoiDiemTao) = YEAR(CURDATE())
    GROUP BY MONTH(thoiDiemTao)
    ORDER BY thang
");
$monthlyStats = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống kê - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/admin.css?v=<?php echo time(); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <div class="top-bar">
                <h1>Thống kê hệ thống</h1>
            </div>
            
            <div class="content-area">
                <!-- Thống kê tổng quan -->
                <div class="stats-grid">
                    <div class="stat-card blue">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo number_format($stats['totalUsers']); ?></h3>
                            <p>Tổng người dùng</p>
                        </div>
                    </div>
                    
                    <div class="stat-card gold">
                        <div class="stat-icon">
                            <i class="fas fa-crown"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo number_format($stats['vipUsers']); ?></h3>
                            <p>Thành viên VIP</p>
                        </div>
                    </div>
                    
                    <div class="stat-card green">
                        <div class="stat-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo number_format($stats['totalMatches']); ?></h3>
                            <p>Ghép đôi thành công</p>
                        </div>
                    </div>
                    
                    <div class="stat-card purple">
                        <div class="stat-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo number_format($stats['totalMessages']); ?></h3>
                            <p>Tin nhắn</p>
                        </div>
                    </div>
                </div>
                
                <!-- Biểu đồ -->
                <div class="charts-grid">
                    <div class="chart-card">
                        <h3><i class="fas fa-chart-line"></i> Người dùng hoạt động (7 ngày gần đây)</h3>
                        <canvas id="userChart"></canvas>
                    </div>
                    
                    <div class="chart-card">
                        <h3><i class="fas fa-heart"></i> Ghép đôi (7 ngày gần đây)</h3>
                        <canvas id="matchChart"></canvas>
                    </div>
                </div>
                
                <!-- Bảng thống kê chi tiết -->
                <div class="section-title">
                    <h2>Thống kê chi tiết</h2>
                </div>
                
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Ngày</th>
                                <th>Người dùng hoạt động</th>
                                <th>Ghép đôi mới</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $days = [];
                            for ($i = 6; $i >= 0; $i--) {
                                $date = date('Y-m-d', strtotime("-$i days"));
                                $days[$date] = ['users' => 0, 'matches' => 0];
                            }
                            
                            foreach ($dailyUsers as $row) {
                                if (isset($days[$row['ngay']])) {
                                    $days[$row['ngay']]['users'] = $row['soLuong'];
                                }
                            }
                            
                            foreach ($dailyMatches as $row) {
                                if (isset($days[$row['ngay']])) {
                                    $days[$row['ngay']]['matches'] = $row['soLuong'];
                                }
                            }
                            
                            foreach ($days as $date => $data):
                            ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($date)); ?></td>
                                <td><?php echo number_format($data['users']); ?></td>
                                <td><?php echo number_format($data['matches']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <script>
    // Dữ liệu cho biểu đồ
    const userChartData = {
        labels: [<?php 
            foreach (array_keys($days) as $date) {
                echo "'" . date('d/m', strtotime($date)) . "',";
            }
        ?>],
        datasets: [{
            label: 'Người dùng',
            data: [<?php 
                foreach ($days as $data) {
                    echo $data['users'] . ',';
                }
            ?>],
            borderColor: '#007bff',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            tension: 0.4
        }]
    };
    
    const matchChartData = {
        labels: [<?php 
            foreach (array_keys($days) as $date) {
                echo "'" . date('d/m', strtotime($date)) . "',";
            }
        ?>],
        datasets: [{
            label: 'Ghép đôi',
            data: [<?php 
                foreach ($days as $data) {
                    echo $data['matches'] . ',';
                }
            ?>],
            borderColor: '#28a745',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            tension: 0.4
        }]
    };
    
    // Vẽ biểu đồ
    new Chart(document.getElementById('userChart'), {
        type: 'line',
        data: userChartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            }
        }
    });
    
    new Chart(document.getElementById('matchChart'), {
        type: 'line',
        data: matchChartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            }
        }
    });
    </script>
</body>
</html>
