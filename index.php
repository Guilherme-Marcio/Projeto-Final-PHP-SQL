<?php
// Conexão com o banco de dados PostgreSQL
$bd = "host=127.0.0.1 port=5432 dbname=postgres user=postgres password=123";
$conexao = pg_connect($bd);

if (!$conexao) {
    die("Erro ao conectar ao banco de dados.");
}

// 1. Transferências da janela com JOIN no jogador e duas vezes no clube (origem e destino)
$query_transferencias = "
select b.jogador, b.posicao, b.nacionalidade,
extract(year from age(a.data_transferencia, b.data_nascimento)) as idade,
c.clube as origem_nome, c.sigla as origem_sigla, c.cor as origem_cor,
d.clube as destino_nome, d.sigla as destino_sigla, d.cor as destino_cor,
a.valor, a.tipo, to_char(a.data_transferencia, 'DD/MM/YYYY') as data
from transferencia a
inner join jogador b on b.id = a.fk_id_jogador
inner join clube c on c.id = a.fk_id_clube_origem
inner join clube d on d.id = a.fk_id_clube_destino
order by a.data_transferencia asc
";
$result_transferencias = pg_query($conexao, $query_transferencias);

// 2. Balanço de cada clube: vendas, compras e saldo (vendas - compras)
$query_balanco = "
select a.clube, a.sigla, a.cor,
sum(case when a.id = b.fk_id_clube_origem then b.valor else 0 end) as vendas,
sum(case when a.id = b.fk_id_clube_destino then b.valor else 0 end) as compras,
sum(
    case
        when a.id = b.fk_id_clube_origem then b.valor
        when a.id = b.fk_id_clube_destino then b.valor * -1
        else 0
    end
) as saldo
from clube a
inner join transferencia b on b.fk_id_clube_origem = a.id or b.fk_id_clube_destino = a.id
group by a.id, a.clube, a.sigla, a.cor
order by saldo desc, vendas desc
";
$result_balanco = pg_query($conexao, $query_balanco);

// 3. Investimento por liga: contratações e total gasto pelos clubes de cada liga
$query_ligas = "
select a.liga, a.pais, count(c.id) as contratacoes, sum(c.valor) as total_gasto
from liga a
inner join clube b on b.fk_id_liga = a.id
inner join transferencia c on c.fk_id_clube_destino = b.id
group by a.id, a.liga, a.pais
order by total_gasto desc
";
$result_ligas = pg_query($conexao, $query_ligas);

// Mostra o valor no formato "€ 45,0 mi"
function milhoes($valor) {
    return "€ " . number_format($valor, 1, ",", ".") . " mi";
}
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
            color: #222;
        }
        h1 {
            margin: 0;
        }
        .subtitulo {
            color: #666;
            font-size: 14px;
            margin: 6px 0 25px 0;
        }
        h2 {
            font-size: 18px;
            margin: 30px 0 12px 0;
        }
        .transferencia {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 20px;
            margin-bottom: 10px;
            background-color: #ffffff;
            border: 1px solid #e2e2e2;
            border-radius: 6px;
            width: 760px;
        }
        .info-jogador {
            width: 190px;
            font-size: 13px;
            color: #555;
        }
        .info-jogador strong {
            display: block;
            font-size: 15px;
            color: #000;
        }
        .negocio {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            flex: 1;
            font-size: 14px;
            font-weight: bold;
        }
        .clube-nome {
            width: 120px;
        }
        .origem {
            text-align: right;
        }
        .escudo {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            color: #ffffff;
            font-size: 12px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .escudo-mini {
            width: 30px;
            height: 30px;
            font-size: 10px;
        }
        .seta {
            color: #888;
            font-size: 18px;
        }
        .info-valor {
            width: 120px;
            text-align: right;
            font-size: 12px;
            color: #555;
        }
        .info-valor strong {
            display: block;
            font-size: 15px;
            color: #000;
        }
        table {
            border-collapse: collapse;
            width: 760px;
            text-align: center;
            background-color: #ffffff;
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
        .col-clube {
            display: flex;
            align-items: center;
            gap: 10px;
            text-align: left;
        }
        .positivo {
            color: #15803d;
            font-weight: bold;
        }
        .negativo {
            color: #b91c1c;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <h1>Mercado da Bola</h1>
    <p class="subtitulo">Janela de transferências de janeiro/2026 (dados fictícios)</p>

    <!-- Listagem das transferências -->
    <h2>Transferências</h2>
    <?php while ($row = pg_fetch_object($result_transferencias)): ?>
        <div class="transferencia">
            <div class="info-jogador">
                <strong><?php echo $row->jogador; ?></strong>
                <span><?php echo $row->posicao; ?> · <?php echo $row->idade; ?> anos · <?php echo $row->nacionalidade; ?></span>
            </div>

            <div class="negocio">
                <span class="clube-nome origem"><?php echo $row->origem_nome; ?></span>
                <span class="escudo" style="background-color: <?php echo $row->origem_cor; ?>"><?php echo $row->origem_sigla; ?></span>
                <span class="seta">&rarr;</span>
                <span class="escudo" style="background-color: <?php echo $row->destino_cor; ?>"><?php echo $row->destino_sigla; ?></span>
                <span class="clube-nome"><?php echo $row->destino_nome; ?></span>
            </div>

            <div class="info-valor">
                <strong><?php echo $row->valor > 0 ? milhoes($row->valor) : "Sem custo"; ?></strong>
                <span><?php echo $row->tipo; ?> · <?php echo $row->data; ?></span>
            </div>
        </div>
    <?php endwhile; ?>

    <!-- Balanço financeiro dos clubes -->
    <h2>Balanço dos clubes</h2>
    <table>
        <thead>
            <tr>
                <th>Pos</th>
                <th>Clube</th>
                <th>Vendas</th>
                <th>Compras</th>
                <th>Saldo</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $posicao = 1;
            while ($row = pg_fetch_object($result_balanco)):
            ?>
                <tr>
                    <td><?php echo $posicao++; ?>º</td>
                    <td class="col-clube">
                        <span class="escudo escudo-mini" style="background-color: <?php echo $row->cor; ?>"><?php echo $row->sigla; ?></span>
                        <?php echo $row->clube; ?>
                    </td>
                    <td><?php echo milhoes($row->vendas); ?></td>
                    <td><?php echo milhoes($row->compras); ?></td>
                    <td class="<?php echo $row->saldo >= 0 ? "positivo" : "negativo"; ?>"><?php echo milhoes($row->saldo); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Investimento por liga -->
    <h2>Investimento por liga</h2>
    <table>
        <thead>
            <tr>
                <th>Liga</th>
                <th>País</th>
                <th>Contratações</th>
                <th>Total gasto</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = pg_fetch_object($result_ligas)): ?>
                <tr>
                    <td><?php echo $row->liga; ?></td>
                    <td><?php echo $row->pais; ?></td>
                    <td><?php echo $row->contratacoes; ?></td>
                    <td><?php echo milhoes($row->total_gasto); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</body>
</html>
<?php
pg_close($conexao);
?>
