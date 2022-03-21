<!-- Testando se a variavel method esta definida -->
<?php echo 'Este é o método solicitado: ' . ($method ?? 'A variavel `method` não foi declarada pelo servidor'); ?>
<hr>
<?php var_dump($method ?? null); ?>