<?php
$bd = "host=127.0.0.1 port=5432 dbname=postgres user=postgres password=123";
$conexao = pg_connect($bd);

if (!$conexao) {
    die("Erro ao conectar ao banco de dados.");
}

$query_transferencias = "
select b.jogador, b.posicao, b.nacionalidade, c.clube as origem, d.clube as destino, a.valor
from transferencia a
inner join jogador b on b.id = a.fk_id_jogador
inner join clube c on c.id = a.fk_id_clube_origem
inner join clube d on d.id = a.fk_id_clube_destino
order by a.valor desc
";
$result_transferencias = pg_query($conexao, $query_transferencias);

$query_compras = "
select b.clube, count(a.id) as contratacoes, sum(a.valor) as total_gasto
from transferencia a
inner join clube b on b.id = a.fk_id_clube_destino
group by b.clube
having count(a.id) >= 2
order by total_gasto desc
";
$result_compras = pg_query($conexao, $query_compras);

$query_vendas = "
select b.clube, count(a.id) as vendas, sum(a.valor) as total_vendido
from transferencia a
inner join clube b on b.id = a.fk_id_clube_origem
group by b.clube
having sum(a.valor) >= 100
order by total_vendido desc
";
$result_vendas = pg_query($conexao, $query_vendas);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Mercado da Bola</title>
    <style>
        body {
            background-color: #f4f6f8;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 30px 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        h1 {
            margin: 0;
        }
        p {
            color: #666;
            font-size: 14px;
        }
        h2 {
            font-size: 18px;
            margin-top: 30px;
        }
        table {
            border-collapse: collapse;
            width: 760px;
            background-color: #ffffff;
            text-align: center;
        }
        th, td {
            border: 1px solid #dddddd;
            padding: 10px;
            font-size: 14px;
        }
        th {
            background-color: #1f2937;
            color: #ffffff;
        }
    </style>
</head>
<body>

    <h1>Mercado da Bola</h1>
    <p>Maiores transferências da janela de verão europeia de 2026 (valores em milhões de libras)</p>

    <h2>Transferências</h2>
    <table>
        <tr>
            <th>Jogador</th>
            <th>Posição</th>
            <th>Nacionalidade</th>
            <th>Saiu de</th>
            <th>Foi para</th>
            <th>Valor</th>
        </tr>
        <?php while ($row = pg_fetch_object($result_transferencias)): ?>
            <tr>
                <td><?php echo $row->jogador; ?></td>
                <td><?php echo $row->posicao; ?></td>
                <td><?php echo $row->nacionalidade; ?></td>
                <td><?php echo $row->origem; ?></td>
                <td><?php echo $row->destino; ?></td>
                <td>£ <?php echo $row->valor; ?> mi</td>
            </tr>
        <?php endwhile; ?>
    </table>

    <h2>Clubes com 2 ou mais contratações</h2>
    <table>
        <tr>
            <th>Clube</th>
            <th>Contratações</th>
            <th>Total gasto</th>
        </tr>
        <?php while ($row = pg_fetch_object($result_compras)): ?>
            <tr>
                <td><?php echo $row->clube; ?></td>
                <td><?php echo $row->contratacoes; ?></td>
                <td>£ <?php echo $row->total_gasto; ?> mi</td>
            </tr>
        <?php endwhile; ?>
    </table>

    <h2>Clubes que venderam £ 100 mi ou mais</h2>
    <table>
        <tr>
            <th>Clube</th>
            <th>Vendas</th>
            <th>Total vendido</th>
        </tr>
        <?php while ($row = pg_fetch_object($result_vendas)): ?>
            <tr>
                <td><?php echo $row->clube; ?></td>
                <td><?php echo $row->vendas; ?></td>
                <td>£ <?php echo $row->total_vendido; ?> mi</td>
            </tr>
        <?php endwhile; ?>
    </table>

</body>
</html>
<?php
pg_close($conexao);
?>
