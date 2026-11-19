drop table if exists transferencia;
drop table if exists jogador;
drop table if exists clube;

create table clube (id serial primary key, clube varchar(100), pais varchar(100));
create table jogador (id serial primary key, jogador varchar(100), posicao varchar(50), nacionalidade varchar(100));
create table transferencia
(id serial primary key, fk_id_jogador int references jogador(id),
fk_id_clube_origem int references clube(id), fk_id_clube_destino int references clube(id),
valor numeric(6,1));

insert into clube (clube, pais) values
('Chelsea','Inglaterra'),
('Manchester City','Inglaterra'),
('Paris Saint-Germain','França'),
('Liverpool','Inglaterra'),
('Aston Villa','Inglaterra'),
('Nottingham Forest','Inglaterra'),
('Newcastle','Inglaterra'),
('Tottenham','Inglaterra'),
('West Ham','Inglaterra'),
('Arsenal','Inglaterra'),
('Brighton','Inglaterra'),
('Manchester United','Inglaterra'),
('Barcelona','Espanha'),
('Everton','Inglaterra'),
('Rennes','França'),
('Al Hilal','Arábia Saudita');

insert into jogador (jogador, posicao, nacionalidade) values
('Enzo Fernández','Meia','Argentina'),
('Bradley Barcola','Atacante','França'),
('Morgan Rogers','Meia','Inglaterra'),
('Elliot Anderson','Meia','Inglaterra'),
('Sandro Tonali','Volante','Itália'),
('Mateus Fernandes','Meia','Portugal'),
('Savinho','Atacante','Brasil'),
('Bruno Guimarães','Volante','Brasil'),
('Carlos Baleba','Volante','Camarões'),
('Rodri','Volante','Espanha'),
('Nicolas Jackson','Atacante','Senegal'),
('Iliman Ndiaye','Atacante','Senegal'),
('Jérémy Jacquet','Zagueiro','França'),
('Crysencio Summerville','Atacante','Holanda');

insert into transferencia (fk_id_jogador, fk_id_clube_origem, fk_id_clube_destino, valor) values
(1,1,2,125),
(2,3,4,123),
(3,5,1,117),
(4,6,2,116),
(5,7,8,100),
(6,9,8,85),
(7,2,8,85),
(8,7,10,75),
(9,11,12,65),
(10,2,13,65),
(11,1,5,65),
(12,14,2,60),
(13,15,4,60),
(14,9,16,60);


select b.jogador, b.posicao, b.nacionalidade, c.clube as origem, d.clube as destino, a.valor
from transferencia a
inner join jogador b on b.id = a.fk_id_jogador
inner join clube c on c.id = a.fk_id_clube_origem
inner join clube d on d.id = a.fk_id_clube_destino
order by a.valor desc;


select b.clube, count(a.id) as contratacoes, sum(a.valor) as total_gasto
from transferencia a
inner join clube b on b.id = a.fk_id_clube_destino
group by b.clube
having count(a.id) >= 2
order by total_gasto desc;


select b.clube, count(a.id) as vendas, sum(a.valor) as total_vendido
from transferencia a
inner join clube b on b.id = a.fk_id_clube_origem
group by b.clube
having sum(a.valor) >= 100
order by total_vendido desc;
