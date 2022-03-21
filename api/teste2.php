<!-- 
    Testando se o 404 apareceu por conta de um erro interno,
    se nada aparecer na tela então essa é a resposta 
-->
<h1>Simulando um erro</h1>
<?php
$a = 3;
$b = 7;
$c = 'Não é numero';
echo ($a + $b);
echo ($a + $c);//Não é possivel somar string e numero