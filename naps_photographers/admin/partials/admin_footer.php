</div><!-- end admin-wrapper -->
<script src="../assets/js/script.js"></script>
<script>
// Sidebar toggle
document.getElementById('sidebarToggle')?.addEventListener('click', () => {
  document.getElementById('adminSidebar')?.classList.toggle('open');
});
// Confirm dialogs
document.querySelectorAll('[data-confirm]').forEach(el => {
  el.addEventListener('click', e => { if(!confirm(el.dataset.confirm)) e.preventDefault(); });
});
</script>
</body>
</html>
