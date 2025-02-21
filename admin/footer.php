                    <!-- Container-fluid closed -->
                    </div>
                <!-- Content section closed -->
            </section>
            <!-- Content-wrapper closed -->
        </div>

        <footer class="main-footer">
            <div class="float-right d-none d-sm-inline">
                Admin Panel <strong><a href="https://iqbolshoh.uz/">Iqbolshoh.uz</a></strong>
            </div>
            <strong> &copy;<?php echo date('Y'); ?>
                <a href="<?= SITE_PATH ?>"><?= $_SERVER['HTTP_HOST'] ?></a>.
            </strong> All rights reserved.
        </footer>

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