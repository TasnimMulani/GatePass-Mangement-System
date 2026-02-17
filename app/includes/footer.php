<!-- app/includes/footer.php -->
    </main> <!-- Closing main-content -->
</div> <!-- Closing dashboard-wrapper -->

<!-- JavaScript Files -->
<script src="public/js/script.js"></script>
<script>
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
