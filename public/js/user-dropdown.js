// Toggle dropdown menu
document.addEventListener('DOMContentLoaded', function() {
    const userAvatar = document.querySelector('.user-avatar-btn');
    const dropdownMenu = document.querySelector('.dropdown-menu');
    
    if (userAvatar && dropdownMenu) {
        userAvatar.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdownMenu.classList.toggle('show');
        });
        
        // Đóng dropdown khi click bên ngoài
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.user-menu')) {
                dropdownMenu.classList.remove('show');
            }
        });
    }
});
