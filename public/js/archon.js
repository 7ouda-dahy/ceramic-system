document.addEventListener('DOMContentLoaded', function () {
    const quickSearch = document.getElementById('archonQuickSearch');

    if (quickSearch) {
        quickSearch.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });
    }
});