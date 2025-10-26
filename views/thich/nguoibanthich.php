<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Thích - Kết Nối Yêu Thương</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/likes.css">
</head>
<body>
    <div class="likes-container">
        <!-- Close Button -->
        <button class="close-btn" onclick="window.location.href='../trangchu/index.php'">
            <i class="fas fa-times"></i>
        </button>

        <!-- Header -->
        <div class="likes-header">
            <h1>Danh Sách Thích</h1>
            <p>Quản lý danh sách những người bạn đã thích.</p>
        </div>

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <input type="text" class="search-input" placeholder="Tìm kiếm theo tên...">
        </div>

        <!-- Likes Grid -->
        <div class="likes-grid">
            <!-- Person 1 -->
            <div class="like-card">
                <span class="badge-new">Đã thích</span>
                <div class="card-avatar">
                    <img src="https://i.pravatar.cc/150?img=5" alt="Nguyễn Thị Mai">
                </div>
                <div class="card-info">
                    <h3>Nguyễn Thị Mai</h3>
                    <p class="card-year">1998 - TP.HCM</p>
                    <p class="card-status">Độc thân</p>
                    <p class="card-date">Thích lúc: 02/04/2025</p>
                </div>
            </div>

            <!-- Person 2 -->
            <div class="like-card">
                <span class="badge-new">Đã thích</span>
                <div class="card-avatar">
                    <img src="https://i.pravatar.cc/150?img=33" alt="Trần Văn Hưng">
                </div>
                <div class="card-info">
                    <h3>Trần Văn Hưng</h3>
                    <p class="card-year">1995 - Hà Nội</p>
                    <p class="card-status">Độc thân</p>
                    <p class="card-date">Thích lúc: 15/04/2025</p>
                </div>
            </div>

            <!-- Person 3 -->
            <div class="like-card">
                <span class="badge-new">Đã thích</span>
                <div class="card-avatar">
                    <img src="https://i.pravatar.cc/150?img=9" alt="Lê Thu Thảo">
                </div>
                <div class="card-info">
                    <h3>Lê Thu Thảo</h3>
                    <p class="card-year">1997 - TP.HCM</p>
                    <p class="card-status">Độc thân</p>
                    <p class="card-date">Thích lúc: 03/04/2025</p>
                </div>
            </div>

            <!-- Person 4 -->
            <div class="like-card">
                <span class="badge-new">Đã thích</span>
                <div class="card-avatar">
                    <img src="https://i.pravatar.cc/150?img=15" alt="Pham Minh Duc">
                </div>
                <div class="card-info">
                    <h3>Pham Minh Duc</h3>
                    <p class="card-year">1993 - Đà Nẵng</p>
                    <p class="card-status">Độc thân</p>
                    <p class="card-date">Thích lúc: 04/04/2025</p>
                </div>
            </div>

            <!-- Person 5 -->
            <div class="like-card">
                <span class="badge-new">Đã thích</span>
                <div class="card-avatar">
                    <img src="https://i.pravatar.cc/150?img=20" alt="Vũ Kim Ngân">
                </div>
                <div class="card-info">
                    <h3>Vũ Kim Ngân</h3>
                    <p class="card-year">1996 - TP.HCM</p>
                    <p class="card-status">Độc thân</p>
                    <p class="card-date">Thích lúc: 02/04/2025</p>
                </div>
            </div>

            <!-- Person 6 -->
            <div class="like-card">
                <span class="badge-new">Đã thích</span>
                <div class="card-avatar">
                    <img src="https://i.pravatar.cc/150?img=12" alt="Hoang Gia Bao">
                </div>
                <div class="card-info">
                    <h3>Hoang Gia Bao</h3>
                    <p class="card-year">1994 - Cần Thơ</p>
                    <p class="card-status">Độc thân</p>
                    <p class="card-date">Thích lúc: 28/08/2025</p>
                </div>
            </div>

            <!-- Person 7 -->
            <div class="like-card">
                <span class="badge-new">Đã thích</span>
                <div class="card-avatar">
                    <img src="https://i.pravatar.cc/150?img=30" alt="Đình Thúc Huỳ">
                </div>
                <div class="card-info">
                    <h3>Đình Thúc Huỳ</h3>
                    <p class="card-year">1997 - Hải Nội</p>
                    <p class="card-status">Độc thân</p>
                    <p class="card-date">Thích lúc: 26/08/2025</p>
                </div>
            </div>

            <!-- Person 8 -->
            <div class="like-card">
                <span class="badge-new">Đã thích</span>
                <div class="card-avatar">
                    <img src="https://i.pravatar.cc/150?img=27" alt="Bùi Thanh Tùng">
                </div>
                <div class="card-info">
                    <h3>Bùi Thanh Tùng</h3>
                    <p class="card-year">1998 - Cần Nông</p>
                    <p class="card-status">Độc thân</p>
                    <p class="card-date">Thích lúc: 21/08/2025</p>
                </div>
            </div>

            <!-- Person 9 -->
            <div class="like-card">
                <span class="badge-new">Đã thích</span>
                <div class="card-avatar">
                    <img src="https://i.pravatar.cc/150?img=25" alt="Trần Tấn Mỹ">
                </div>
                <div class="card-info">
                    <h3>Trần Tấn Mỹ</h3>
                    <p class="card-year">1997 - Đà Nẵng</p>
                    <p class="card-status">Độc thân</p>
                    <p class="card-date">Thích lúc: 18/04/2025</p>
                </div>
            </div>

            <!-- Person 10 -->
            <div class="like-card">
                <span class="badge-new">Đã thích</span>
                <div class="card-avatar">
                    <img src="https://i.pravatar.cc/150?img=32" alt="Nguyễn Quang Anh">
                </div>
                <div class="card-info">
                    <h3>Nguyễn Quang Anh</h3>
                    <p class="card-year">1993 - Hà Nội</p>
                    <p class="card-status">Độc thân</p>
                    <p class="card-date">Thích lúc: 16/08/2025</p>
                </div>
            </div>

            <!-- Person 11 -->
            <div class="like-card">
                <span class="badge-new">Đã thích</span>
                <div class="card-avatar">
                    <img src="https://i.pravatar.cc/150?img=47" alt="Lý Thúy Nga">
                </div>
                <div class="card-info">
                    <h3>Lý Thúy Nga</h3>
                    <p class="card-year">1994 - TP.HCM</p>
                    <p class="card-status">Độc thân</p>
                    <p class="card-date">Thích lúc: 04/04/2025</p>
                </div>
            </div>

            <!-- Person 12 -->
            <div class="like-card">
                <span class="badge-new">Đã thích</span>
                <div class="card-avatar">
                    <img src="https://i.pravatar.cc/150?img=36" alt="Dương Quốc Vinh">
                </div>
                <div class="card-info">
                    <h3>Dương Quốc Vinh</h3>
                    <p class="card-year">1997 - Cần Nông</p>
                    <p class="card-status">Độc thân</p>
                    <p class="card-date">Thích lúc: 10/08/2025</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Search functionality
        const searchInput = document.querySelector('.search-input');
        const likeCards = document.querySelectorAll('.like-card');

        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            
            likeCards.forEach(card => {
                const name = card.querySelector('h3').textContent.toLowerCase();
                if (name.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Filter tabs functionality
        const filterBtns = document.querySelectorAll('.filter-btn');
        
        filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active class from all buttons
                filterBtns.forEach(b => b.classList.remove('active'));
                // Add active class to clicked button
                this.classList.add('active');
                
                // Here you can add filtering logic based on button text
                console.log('Filter by:', this.textContent);
            });
        });

        // Card click to view profile
        likeCards.forEach(card => {
            card.addEventListener('click', function() {
                const name = this.querySelector('h3').textContent;
                console.log('View profile:', name);
                // You can redirect to profile page here
                // window.location.href = '../hoso/xemnguoikhac.php?id=...';
            });
        });
    </script>
</body>
</html>
