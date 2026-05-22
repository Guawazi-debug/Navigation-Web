// 通用JS功能
document.addEventListener('DOMContentLoaded', function() {
    // 搜索框回车提交
    const searchInput = document.querySelector('.search-box input');
    if (searchInput) {
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                this.form.submit();
            }
        });
    }
});
