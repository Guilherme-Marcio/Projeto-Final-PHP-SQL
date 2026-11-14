-- Trabalho Prova II - Banco de Dados I
-- Tema: Mercado da Bola (janela de transferencias ficticia de janeiro/2026)

-- apaga as tabelas caso o script seja executado mais de uma vez
drop table if exists transferencia;
drop table if exists jogador;
drop table if exists clube;
drop table if exists liga;

create table liga (id serial primary key, liga varchar(100), pais varchar(100));

-- sigla e cor sao usadas para desenhar o "escudo" do clube na pagina
create table clube
(id serial primary key, clube varchar(100), sigla varchar(3), cor varchar(7),
fk_id_liga int references liga(id));

create table jogador
(id serial primary key, jogador varchar(100), posicao varchar(50),
nacionalidade varchar(100), data_nascimento date);

-- valor em milhoes de euros. A mesma tabela clube e referenciada duas vezes (origem e destino)
create table transferencia
(id serial primary key, fk_id_jogador int references jogador(id),
fk_id_clube_origem int references clube(id),
fk_id_clube_destino int references clube(id),
valor numeric(6,1), tipo varchar(20), data_transferencia date);

insert into liga (liga, pais) values
('Brasileirão','Brasil'),
('La Liga','Espanha'),
('Premier League','Inglaterra'),
('Serie A','Itália');

insert into clube (clube, sigla, cor, fk_id_liga) values
('Flamengo','FLA','#c8102e',1),
('Palmeiras','PAL','#006437',1),
('Cruzeiro','CRU','#0033a0',1),
('Real Madrid','RMA','#b8962e',2),
('Barcelona','BAR','#a50044',2),
('Manchester City','MCI','#6cabdd',3),
('Liverpool','LIV','#d00027',3),
('Juventus','JUV','#222222',4);

insert into jogador (jogador, posicao, nacionalidade, data_nascimento) values
('Vitor Roque','Atacante','Brasil','2005-02-28'),
('Pedro','Atacante','Brasil','1997-06-20'),
('Kaio Jorge','Atacante','Brasil','2002-01-24'),
('Rodrygo','Atacante','Brasil','2001-01-09'),
('Bernardo Silva','Meia','Portugal','1994-08-10'),
('Federico Chiesa','Atacante','Itália','1997-10-25'),
('Raphael Veiga','Meia','Brasil','1995-06-19'),
('Kenan Yildiz','Meia','Turquia','2005-05-04'),
('Gavi','Meia','Espanha','2004-08-05'),
('Matheus Pereira','Meia','Brasil','1996-05-05');

insert into transferencia (fk_id_jogador, fk_id_clube_origem, fk_id_clube_destino,
valor, tipo, data_transferencia) values
(1,2,7,45.0,'Compra','2026-01-02'),
(4,4,6,80.0,'Compra','2026-01-05'),
(3,3,1,18.5,'Compra','2026-01-08'),
(2,1,8,22.0,'Compra','2026-01-10'),
(5,6,5,0.0,'Livre','2026-01-12'),
(8,8,4,70.0,'Compra','2026-01-15'),
(6,7,8,3.0,'Empréstimo','2026-01-18'),
(7,2,3,9.0,'Compra','2026-01-20'),
(9,5,6,55.0,'Compra','2026-01-25'),
(10,3,2,14.0,'Compra','2026-01-30');


-- 1. Transferencias da janela: jogador, idade na data do negocio, origem e destino
select b.jogador, b.posicao, b.nacionalidade,
extract(year from age(a.data_transferencia, b.data_nascimento)) as idade,
c.clube as origem_nome, c.sigla as origem_sigla, c.cor as origem_cor,
d.clube as destino_nome, d.sigla as destino_sigla, d.cor as destino_cor,
a.valor, a.tipo, to_char(a.data_transferencia, 'DD/MM/YYYY') as data
from transferencia a
inner join jogador b on b.id = a.fk_id_jogador
inner join clube c on c.id = a.fk_id_clube_origem
inner join clube d on d.id = a.fk_id_clube_destino
order by a.data_transferencia asc;


-- 2. Balanco de cada clube: quanto vendeu, quanto comprou e o saldo (vendas - compras)
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
order by saldo desc, vendas desc;


-- 3. Investimento por liga: quantos jogadores chegaram e quanto os clubes da liga gastaram
select a.liga, a.pais, count(c.id) as contratacoes, sum(c.valor) as total_gasto
from liga a
inner join clube b on b.fk_id_liga = a.id
inner join transferencia c on c.fk_id_clube_destino = b.id
group by a.id, a.liga, a.pais
order by total_gasto desc;
