<?php $_footer_logado = $this->ion_auth->logged_in(); ?>
<?php if ($_footer_logado) : ?>
    </main><!-- #conteudo -->
<?php else : ?>
    </main><!-- pagina-publica -->
<?php endif ?>

<!-- jQuery (DataTables + máscaras precisam dele) -->
<script src="<?= asset_url('assets/js/bs5/jquery.min.js') ?>"></script>
<!-- Bootstrap 5 bundle (collapse, dropdown, etc.) -->
<script src="<?= asset_url('assets/js/bs5/bootstrap.bundle.min.js') ?>"></script>
<!-- DataTables + integração Bootstrap 5 -->
<script src="<?= asset_url('assets/js/bs5/jquery.dataTables.min.js') ?>"></script>
<script src="<?= asset_url('assets/js/bs5/dataTables.bootstrap5.min.js') ?>"></script>
<!-- Máscaras -->
<script src="<?= asset_url('assets/js/bs5/jquery.mask.min.js') ?>"></script>
<!-- Scripts do sistema (cache-busting via filemtime em asset_url()) -->
<script src="<?= asset_url('assets/js/main.js') ?>"></script>

</body>
</html>
