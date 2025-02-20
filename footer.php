
                    
                    <!-- Container-fluid closed -->
                    </div>
                <!-- Content section closed -->
            </section>
            <!-- Content-wrapper closed -->
        </div>

        <footer class="main-footer">
            <div class="float-right d-none d-sm-inline">
                Admin Panel
            </div>
            <strong>Iqbolshoh &copy;2025 <a href="https://Iqbolshoh.uz">Iqbolshoh.uz</a>.</strong> All rights reserved.
        </footer>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="<?= SITE_PATH ?>/src/js/jquery.min.js"></script>
        <script src="<?= SITE_PATH ?>/src/js/bootstrap.bundle.min.js"></script>
        <script src="<?= SITE_PATH ?>/src/js/adminlte.min.js"></script>
        <script>
            function logout() {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You will be logged out!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, log me out!'
                }).then((result) => {
                    if (result.value) {
                        window.location.href = '<?= SITE_PATH ?>/logout/';
                    }
                });
            }
        </script>

        <!-- Wrapper closed -->
    </div>
    <!-- Body closed -->
</body>

</html>