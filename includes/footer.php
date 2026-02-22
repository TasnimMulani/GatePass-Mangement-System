<!-- includes/footer.php -->
</div> <!-- Closing main-content div -->
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            function initToggle() {
                const sidebarCollapse = document.getElementById('sidebarCollapse');
                const sidebar = document.querySelector('.sidebar');
                const mainContent = document.querySelector('.main-content');
                
                if (sidebarCollapse && sidebar && mainContent) {
                    sidebarCollapse.addEventListener('click', function() {
                        sidebar.classList.toggle('collapsed');
                        mainContent.classList.toggle('expanded');
                        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
                    });
                    
                    // Restore state
                    if (localStorage.getItem('sidebarCollapsed') === 'true') {
                        sidebar.classList.add('collapsed');
                        mainContent.classList.add('expanded');
                    }
                }
            }
            initToggle();
        });

        // JavaScript for print functionality
        function printPass() {
            var printContent = document.getElementById('printableArea').innerHTML;
            var originalContent = document.body.innerHTML;
            document.body.innerHTML = printContent;
            window.print();
            document.body.innerHTML = originalContent;
            location.reload();
        }
    </script>
</body>
</html>
