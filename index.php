<html>

<head>
    <title>Gerador de Relatórios</title>
</head>

<body>
    <button onclick="
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'relatorio.php');
            xhr.send();
            xhr.onreadystatechange = () => {
            var DONE = 4;
            var OK = 200;
            if(xhr.readyState===DONE){
                if (xhr.status === OK) {
                    console.log(xhr.responseText);
                } else {
                    console.log('Erro: ' + xhr.status);
                }
            }
        }
    ">Gerar Relatório</button>
</body>

</html>