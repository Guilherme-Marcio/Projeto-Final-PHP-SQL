# Mercado da Bola

Trabalho referente à Prova II de Banco de Dados I (4º período - Engenharia de Software).

Banco de dados em PostgreSQL com as maiores transferências da janela de verão europeia de 2026
e uma página em PHP que mostra os dados com `pg_connect`, `pg_query` e `pg_fetch_object`.

## Arquivos

- `mercado_da_bola.sql`: criação das tabelas (DDL), inserção dos dados (DML) e consultas (DQL).
- `index.php`: página que se conecta ao banco e exibe o resultado das consultas.

## Tabelas

| Tabela | Chaves estrangeiras |
| --- | --- |
| `clube` | - |
| `jogador` | - |
| `transferencia` | `fk_id_jogador` -> `jogador`, `fk_id_clube_origem` e `fk_id_clube_destino` -> `clube` |

## Consultas

1. Todas as transferências, com `inner join` em `jogador` e duas vezes em `clube` (origem e destino).
2. Clubes com 2 ou mais contratações: `group by` + `having count(...) >= 2`.
3. Clubes que venderam £ 100 milhões ou mais: `group by` + `having sum(...) >= 100`.

## Fonte dos dados

Valores em milhões de libras, segundo a lista
[Top 20 most expensive summer transfers of 2026](https://www.thenationalnews.com/sport/football/2026/09/23/top-20-most-expensive-summer-transfers-of-2026-including-fernandez-anderson-and-barcola/)
(The National, 23/09/2026), que usa valores divulgados pela imprensa. Foram usadas as 14 transferências da lista com valor em libras.

## Como executar

1. Executar `mercado_da_bola.sql` no pgAdmin (Query Tool do banco `postgres`).
2. Na pasta do projeto, rodar `C:/xampp/php/php.exe -d extension=pgsql -S localhost:8000`.
3. Abrir `http://localhost:8000/index.php`.
