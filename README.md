# Mercado da Bola

Trabalho referente à Prova II de Banco de Dados I (4º período - Engenharia de Software).

Banco de dados em PostgreSQL sobre uma janela de transferências de futebol (dados fictícios),
com uma página em PHP que consulta o banco e mostra as transferências, o balanço financeiro
de cada clube e o investimento por liga.

## Tabelas

| Tabela | Descrição | Chaves estrangeiras |
| --- | --- | --- |
| `liga` | Ligas e seus países | - |
| `clube` | Clubes, com sigla e cor do escudo | `fk_id_liga` -> `liga` |
| `jogador` | Jogadores, posição, nacionalidade e nascimento | - |
| `transferencia` | Negócios da janela, com valor em milhões de euros | `fk_id_jogador` -> `jogador`, `fk_id_clube_origem` e `fk_id_clube_destino` -> `clube` |

## Consultas

1. **Transferências**: `transferencia` com `inner join` em `jogador` e duas vezes em `clube` (origem e destino). A idade é calculada com `age()` na data do negócio.
2. **Balanço dos clubes**: `sum` + `case` para separar o que o clube vendeu (é a origem) do que comprou (é o destino) e calcular o saldo.
3. **Investimento por liga**: `liga` -> `clube` -> `transferencia`, com `count` e `sum` agrupados por liga.

## Como executar

1. Executar o arquivo `mercado_da_bola.sql` no PostgreSQL (pgAdmin ou psql), no banco `postgres`.
2. Ativar a extensão no `php.ini` do XAMPP: remover o `;` da linha `;extension=pgsql` e reiniciar o Apache.
3. Copiar o `index.php` para uma pasta dentro de `C:\xampp\htdocs` e ajustar a senha na linha do `pg_connect`, se for diferente de `123`.
4. Abrir no navegador: `http://localhost/<pasta>/index.php`.
