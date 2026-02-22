<!-- app/includes/footer.php -->
    </main> <!-- Closing main-content -->
</div> <!-- Closing dashboard-wrapper -->

<!-- JavaScript Files -->
<script src="public/js/script.js"></script>
<script>
    // Sidebar toggle functionality
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM Content Loaded - Initializing Sidebar Toggle');
        
        function initToggle() {
            const sidebarCollapse = document.getElementById('sidebarCollapse');
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            
            if (sidebarCollapse && sidebar && mainContent) {
                console.log('Sidebar elements found. Attaching listener.');
                
                // Remove existing listener if any to avoid duplicates
                sidebarCollapse.replaceWith(sidebarCollapse.cloneNode(true));
                const newBtn = document.getElementById('sidebarCollapse');
                
                newBtn.addEventListener('click', function() {
                    console.log('Sidebar toggle clicked');
                    sidebar.classList.toggle('collapsed');
                    mainContent.classList.toggle('expanded');
                    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
                });
                
                // Restore state
                if (localStorage.getItem('sidebarCollapsed') === 'true') {
                    sidebar.classList.add('collapsed');
                    mainContent.classList.add('expanded');
                }
            } else {
                console.error('Sidebar toggle failed: Elements not found', {
                    button: !!sidebarCollapse,
                    sidebar: !!sidebar,
                    main: !!mainContent
                });
            }
        }
        
        initToggle();
    });

    // Print functionality
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
